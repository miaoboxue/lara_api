<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
class UserController extends Controller
{
    //
    protected $redis_h_u_key='user:u:h';
    public function phpinfo(){
        phpinfo();
    }
    public function userLogin(Request $request){
        $user_name=$request->input('name');
        $pass=$request->input('pwd');

        if(1){    //登录成功
            $uid = $request->input('uid');
            $str = time() + $uid + mt_rand(1111,9999);
            $token =substr(md5($str),10,20);


            //保存 token 存入redis
            $key =$this->redis_h_u_key . $uid;
            Redis::hSet($key,'token',$token);
            Redis::expire($key,3600*7);    //过期时间 一周

            $response = [
                'error'     =>  0,
                'token'     =>  $token
            ];

        }else{
            //TODO 登录失败
        }
        return $response;
    }
    //个人中心
    public function uCenter(Request $request){
        $uid = $request->input('uid');
        //print_r($_SERVER);exit;


        if(!empty($_SERVER['HTTP_TOKEN'])){
            $http_token = $_SERVER['HTTP_TOKEN'];
            $key = $this->redis_h_u_key . $uid;

            $token = Redis::hGet($key,'token');
            //echo $token;die;

            if($token == $http_token){
                $response = [
                    'error'     =>  0,
                    'msg'       =>  'ok'
                ];
            }else{
                $response = [
                    'error'     =>  50001,
                    'msg'       =>  'invalid token'
                ];
            }

        }else{
            $response = [
                'errno'     =>  50000,
                'msg'       =>  'not find token'
            ];
        }
        return $response;
    }

    public function order(){
       // echo __METHOD__;
        //echo '<pre>';print_r($_SERVER);echo '</pre>';
        $request_uri = $_SERVER['REQUEST_URI'];
        echo $request_uri;echo '</br>';

        echo md5($request_uri);echo '</br>';

        $uri_hash=substr(md5($request_uri),0,10);
        echo $uri_hash;echo '</br>';

        //客户端ip
        $ip=$_SERVER['REMOTE_ADDR'];
        echo $ip;echo '</br>';

        $redis_key = 'str:'.$uri_hash. ':' .$ip;
        echo $redis_key;echo '</br>';

        $num = Redis::incr($redis_key);
        echo 'couht:'.$num;echo '</br>';

        Redis::expire($redis_key,60);
        if($num>10){   //请求次数超过
            $response=[
                'erron'=>50000,
                'msg'=>'Invalid Reuqest!',
                'ip'=>$ip
            ];
            Redis::expire($redis_key,600); //禁止十分钟
        }else{
            $response=[
                'erron'=>0,
                'msg'=>'ok',
                'data'=>[
                    'aaa'=>'bbb',
                ],
            ];
        }
        return $response;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/12
 * Time: 11:53
 */

namespace app\wx\home;


use app\index\controller\Home;
use app\index\controller\Index;
use think\Cache;
use think\Cookie;
use think\Session;

class Wxtoken   extends Index
{
    //定时刷新accessToken
    public function refreshToken()
    {
        $accessToken=Cache::get('ACCESSTOKEN');
        if(!$accessToken)
        {
            $this->getAccessToken();
            return  Cache::get('ACCESSTOKEN');
        }
    }
    //获取ACCESSTOken
    public function getAccessToken()
    {
//        APPID: NXZvrra2bkYIHPWu
//        secert: f8hU941Ek1vb3wvAqh5nEnTTE2lg4xjd
//        https://openapi.xohaa.net/login
        $appid='NXZvrra2bkYIHPWu';
        $secret='f8hU941Ek1vb3wvAqh5nEnTTE2lg4xjd';
        $url= 'http://openapi.xohaa.net/token?appid=NXZvrra2bkYIHPWu&secret=f8hU941Ek1vb3wvAqh5nEnTTE2lg4xjd&grant_type=authorization_code';
        $data=json_decode(curl_get($url),true);
//        dump($data);
        Cache::set('ACCESSTOKEN',$data['data'],$data['data']['expiresIn']);
//        dump(Cache::get('ACCESSTOKEN'));
    }
    //微信ACCESSTOKEN
    public function wxAccessToken()
    {
        $appid='wx4610885af53f6d69';
        $secret='6fd15a3a419fbd2c252b4023c11b9900';
        $url= 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret.'';
        $data=json_decode(curl_get($url),true);
        Cache::set('WXACCESSTOKEN',$data['access_token'],$data['expires_in']);
    }
    public function refreshWxToken()
    {
        $accessToken=Cache::get('WXACCESSTOKEN');
//        dump($accessToken);
        if(!$accessToken)
        {
            $this->wxAccessToken();

        }
        return  Cache::get('WXACCESSTOKEN');
    }
}
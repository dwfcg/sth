<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/19
 * Time: 11:55
 */

namespace app\wx\home;


use app\index\controller\Index;
use think\Cache;
use think\Db;
use think\Request;

class Lock  extends Index
{
    protected $appid='NXZvrra2bkYIHPWu';
    protected $secret='f8hU941Ek1vb3wvAqh5nEnTTE2lg4xjd';
    protected $accessToken;
    public function __construct()
    {
        $accessToken=Cache::get('ACCESSTOKEN');
        if(!$accessToken)
        {
            $accessToken=(new Wxtoken())->refreshToken();
        }
//        dump($accessToken);
        $this->accessToken=$accessToken['accessToken'];
    }
//获取锁的信息
    public function lockInfo($lnumlist)
    {

//        http://openapi.xohaa.net/ilocks/3QNDELLRV5OE?access_token=ac56a163-fc5f-4b4c-b571-414ed4cbebc5
        $url= 'http://openapi.xohaa.net/ilocks/'.$lnumlist.'?access_token='.$this->accessToken.'';
        $data=json_decode(curl_get($url),true);
//        dump($data);
        return  $data['data'];

    }
    //获取设备是否在线
    public function   isOnline($lnumlist)
    {
//        http://openapi.xohaa.net/ilocks/3QNDELLRV5OE/online?access_token=ac56a163-fc5f-4b4c-b571-414ed4cbebc5
        $url= 'http://openapi.xohaa.net/ilocks/'.$lnumlist.'/online?access_token='.$this->accessToken.'';
        $data=json_decode(curl_get($url),true);
//        dump($data);
        return  $data['data'];
    }
    //更新指定密码组的密码
    public function updateLock($lnumlist)
    {
        $pwd=$this->random_code();
        Db::name('wx_lock')->where('lnumlist',$lnumlist)->update(['pwd'=>$pwd]);
        $url= 'http://openapi.xohaa.net/ilocks/'.$lnumlist.'/password/1?access_token='.$this->accessToken.'&type=1&status=0&password='.$pwd.'&start='.time().'&end='.(time()+86400).'';
        $data=json_decode(curl_post($url),true);
//        dump($data);
        $re=$this->checkCommend($lnumlist,$data['data']);
//        dump($re);
        return  $data['data'];
    }
    public function checkCommend($lnumlist,$commendID)
    {
//        sleep(15);
        $url= 'http://openapi.xohaa.net/ilocks/'.$lnumlist.'/instruction/result?access_token='.$this->accessToken.'&instruction_id='.$commendID.'';
        $data=json_decode(curl_get($url),true);
//        echo $data['data'];
        return  $data['data'];
    }
    //开锁
    public function  unlock($lnumlist,$pwd)
    {
//        http://openapi.xohaa.net/ilocks/3QNDELLRV5OE/open?access_token=d71d9483-2c65-42d5-b962-e08cc0b6c4ea&pwd_index=0&pwd=123456
        $url= 'http://openapi.xohaa.net/ilocks/'.$lnumlist.'/open?access_token='.$this->accessToken.'&pwd_index=1&pwd='.$pwd.'';
        $data=json_decode(curl_post($url),true);
//        dump($data);
//        $re=$this->checkCommend($lnumlist,$data['data']);
        return  $data['data'];
    }
    /**
     * 生成邀请码
     * @param $uid
     * @return string
     */
    function random_code($length = 6,$chars = null){
        if(empty($chars)){
            $chars = '0123456789';
        }
        $count = strlen($chars) - 1;
        $code = '';
        while( strlen($code) < $length){
            $code .= substr($chars,rand(0,$count),1);
        }
        return $code;
    }
}
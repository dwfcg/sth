<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/29
 * Time: 17:27
 */

namespace app\wx\home;


use app\index\controller\Home;
use think\Cache;
use think\Config;
use think\response\Json;
use think\Session;

class Sms   extends Home
{
    // 发送验证码
    public function postsms()
    {
        $data = input('post.');

        if(empty($data['mobile'])){
            $returnData=[
                'msg'=>'手机号填写不正确',
                'code'=>'1025',
            ];
            return   Json::create($returnData);
        }

        $sms_code = rand(111111, 999999);

        $result = plugin_action('DySms/DySms/send', [$data['mobile'], ['code' => $sms_code,'product'=>'瘾家'], '绑定手机号']);
//        dump($result);
//        code==0  发送成功
        if ($result['code']) {
            return Json::create($result);
        } else {
//            Session::set('code', $sms_code);
            Cache::set($sms_code,$sms_code,Config::get('wxpay_config.code_time_out'));

            return Json::create($result);
        }

    }

    public  static function checkCode()
    {
        $data=input('post.');
        if(empty($data['code'])){
//            echo 33;
           return   false;
        }
        $code=Cache::get($data['code']);
        if($code!=$data['code']){
//            echo 22;
            return   false;
        }else{
//            echo 11;
           return   true;
        }
    }
}
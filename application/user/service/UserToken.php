<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/7
 * Time: 16:56
 */

namespace app\user\service;


use app\lib\exception\WeChatException;
use app\user\model\NewUser;
use app\user\model\User;
use app\user\model\WxUser;
use think\Config;
use think\Exception;

class UserToken extends Token
{
    protected $code;
    protected $wxLoginUrl;
    protected $wxAppID;
    protected $wxAppSecret;

    public function __construct($code)
    {
        $this->code = $code;
        $wxpay_config = Config::get('wxpay_config');
        $this->wxAppID = $wxpay_config['app_id'];
        $this->wxAppSecret = $wxpay_config['app_secret'];
        $this->wxLoginUrl = sprintf(
            $wxpay_config['login_url'], $this->wxAppID, $this->wxAppSecret, $this->code);
    }

    public function get()
    {
        $result = curl_get($this->wxLoginUrl);
        $wxresult = json_decode($result, true);
        if (empty($wxresult)) {
            throw new Exception('获取session_key及openID时异常，微信内部错误');
        } else {
            $logfinfail = array_key_exists('errcode', $wxresult);
            if ($logfinfail) {
                $this->processLoginError($wxresult);
            } else {
                return  $this->grantToken($wxresult);
            }
        }
    }
    // 处理微信登陆异常
    // 那些异常应该返回客户端，那些异常不应该返回客户端
    // 需要认真思考
    private function processLoginError($wxResult)
    {
        throw new WeChatException(
            [
                'msg' => $wxResult['errmsg'],
                'errorCode' => $wxResult['errcode']
            ]);
    }

    private function grantToken($wxresult)
    {
        $openid = $wxresult['openid'];
        $user = WxUser::getByOpenID($openid);
        if (!$user) {
            $uid = $this->newUser($openid);
        } else {
            $uid = $user->id;
        }
        $cacheValue=$this->prepareCachedValue($wxresult,$uid);
        $token=$this->saveToCache($cacheValue);
        return  $token;
    }
    private function prepareCachedValue($wxResult, $uid)
    {
        $cachedValue = $wxResult;
        $cachedValue['uid'] = $uid;
        return $cachedValue;
    }

    private function newUser($openid)
    {
        $user = WxUser::create([
            'openid' => $openid
        ]);
        return $user->id;
    }
    // 写入缓存
    private function saveToCache($wxResult)
    {
        $key = self::generateToken();
        $value = json_encode($wxResult);
        $expire_in = '7200';
        $result = cache($key, $value, $expire_in);

        if (!$result){
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005
            ]);
        }
        return $key;
    }
}
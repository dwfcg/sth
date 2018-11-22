<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/7
 * Time: 16:55
 */

namespace app\user\service;


use app\lib\exception\TokenException;
use think\Cache;
use think\Request;

class Token
{

    // 生成令牌
    public static function generateToken()
    {
        $randChar = getRandChar(32);
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        return md5($randChar . $timestamp);
    }
    public static function getCurrentTokenVar($key)
    {
        $token = Request::instance()
            ->header('token');
        $vars = Cache::get($token);
        if (!$vars) {
            throw new TokenException();
        } else {
            if (!is_array($vars)) {
                $vars = json_decode($vars, true);
            }
            if (array_key_exists($key, $vars)) {
                return $vars[$key];
            } else {
                throw new Exception('尝试获取的Token变量并不存在');
            }
        }
    }
    /**
     * 当需要获取全局UID时，应当调用此方法
     *而不应当自己解析UID
     *
     */
    public static function getCurrentUid()
    {
        $uid = self::getCurrentTokenVar('uid');
        return $uid;

    }

    /**
     * 检查操作UID是否合法
     * @param $checkedUID
     * @return bool
     * @throws Exception
     * @throws ParameterException
     */
    public static function isValidOperate($checkedUID)
    {
        if(!$checkedUID){
            throw new Exception('检查UID时必须传入一个被检查的UID');
        }
        $currentOperateUID = self::getCurrentUid();
        if($currentOperateUID == $checkedUID){
            return true;
        }
        return false;
    }

    public static function verifyToken($token)
    {
        $exist = Cache::get($token);
        if($exist){
            return true;
        }
        else{
            return false;
        }
    }
}
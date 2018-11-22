<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/7
 * Time: 13:59
 */

namespace app\user\home;
use app\lib\exception\ParameterException;
use app\user\validate\Token as TokenValidate;
use app\user\service\UserToken;
use think\Db;
use think\response\Json;
use app\user\service\Token as TokenService;

class Token extends Common
{
    public function getToken($code='')
    {
//        Db::name('admin_user')->select();
        (new TokenValidate())->goCheck();
        $wx=new UserToken($code);
        $token=$wx->get();
        $data=['token' => $token];
        return Json::create($data);
    }
    public function verifyToken($token='')
    {
        if(!$token){
            throw new ParameterException([
               'msg'=> 'token不能为空'
            ]);
        }
        $valid= TokenService::verifyToken($token);
        return Json::create(['isvalid'=>$valid]);
    }
}
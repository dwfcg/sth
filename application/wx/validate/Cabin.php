<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 10:49
 */

namespace app\wx\validate;


use think\Validate;

class Cabin extends Validate
{
    //定义验证规则
    protected $rule = [
        'name|昵称' => 'require',
        'address|地区' => 'require',
        'cnumlist|编号'  => 'require|unique:wx_cabin',
    ];

    //定义验证提示
    protected $message = [
        'name.require' => '请输入名称',
//        'address.require'    => '请选择地址',
        'cnumlist.require'    => '编号必须输入',
        'cnumlist.unique:wx_cabin'    => '编号重复',
    ];

    //定义验证场景
    protected $scene = [
//        //更新
//        'edit'  =>  ['email', 'password' => 'length:6,20', 'mobile', 'role'],
//        //登录
//        'add'  =>  ['username' => 'require', 'password' => 'require'],
    ];
}
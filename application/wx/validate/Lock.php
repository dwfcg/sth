<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 10:49
 */

namespace app\wx\validate;


use app\user\validate\BaseValidate;
use think\Validate;

class Lock extends BaseValidate
{
    //定义验证规则
    protected $rule = [
        'lnumlist|锁编号'  => 'require|unique:wx_lock',
        'cnumlist|舱体编号'  => 'require|unique:wx_lock',
    ];

    //定义验证提示
    protected $message = [
        'lnumlist.require'    => '锁编号必须输入',
        'lnumlist.unique:wx_lock'    => '锁编号重复',
        'cnumlist.require'    => '舱体编号必须输入',
        'cnumlist.unique:wx_lock'    => '舱体编号重复',
    ];

    //定义验证场景
    protected $scene = [
//        //更新
//        'edit'  =>  ['email', 'password' => 'length:6,20', 'mobile', 'role'],
//        //登录
//        'add'  =>  ['username' => 'require', 'password' => 'require'],
    ];
}
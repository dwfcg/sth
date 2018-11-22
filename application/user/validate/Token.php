<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/7
 * Time: 15:56
 */

namespace app\user\validate;


use think\Validate;

class Token  extends BaseValidate
{
    protected $rule = [
        'code' => 'require|isNotEmpty'
    ];

    protected $message=[
        'code' => '没有code还想拿token？做梦哦'
    ];
}
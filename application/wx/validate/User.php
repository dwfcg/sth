<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 14:06
 */

namespace app\wx\validate;
use app\user\validate\BaseValidate;

class User  extends BaseValidate
{
    //定义验证规则
    protected $rule = [
        'mobile'  => 'require',
    ];
}
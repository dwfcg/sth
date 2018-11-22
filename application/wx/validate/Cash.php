<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/22
 * Time: 10:54
 */

namespace app\wx\validate;


use app\user\validate\BaseValidate;

class Cash  extends BaseValidate
{
    //定义验证规则
    protected $rule = [
        'cash'  => 'require',
        'level'  => 'require',
    ];
}
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/12
 * Time: 17:36
 */

namespace app\wx\home;


use app\index\controller\Index;
use app\wx\validate\Order;
class Demo  extends Index
{
    public function demo()
    {
        $validate=new Order();
        $validate->goCheck();
        echo 111;
    }
}
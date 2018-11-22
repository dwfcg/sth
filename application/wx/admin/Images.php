<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/20
 * Time: 13:53
 */

namespace app\wx\admin;


use app\admin\controller\Admin;
use app\common\builder\ZBuilder;

class Images    extends Admin
{
    public function createImage()
    {
      return    ZBuilder::make('table')->fetch();
    }
}
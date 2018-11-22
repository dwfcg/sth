<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 10:25
 */

namespace app\wx\model;


use think\Model;

class WxLock   extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__WX_LOCK__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
}
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/8
 * Time: 14:44
 */

namespace app\wx\model;


use think\Model;

class WxOrder    extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__WX_ORDER__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    public function user()
    {
        return  $this->belongsTo('WxUser','uid','id');
    }
}
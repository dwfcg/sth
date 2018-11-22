<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 17:37
 */

namespace app\wx\model;


use think\Model;

class WxCash    extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__WX_CASH__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    public function user()
    {
        return  $this->belongsTo('WxUser','uid','id');
    }
    public function cash($uid)
    {
        $data=self::where('uid',$uid)->where('order_type',0)->select();
        return  $data;
    }
    public function card($uid)
    {
        return  self::with('user')
            ->where('uid',$uid)
            ->where('order_type',1)
            ->select();
    }
}
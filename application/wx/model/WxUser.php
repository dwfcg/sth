<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/8
 * Time: 14:44
 */

namespace app\wx\model;


use think\Model;

class WxUser    extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__WX_USER__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    /**
     * 用户是否存在
     * 存在返回uid，不存在返回0
     */
    public function cash()
    {
        return $this->hasMany('WxCash','uid','id');
    }
    public function order()
    {
        return $this->hasMany('WxOrder','uid','id');
    }
    public static function getByOpenID($openid)
    {
        $user = self::where('openid', $openid)
            ->find();
        return $user;
    }
    public static function getByID($uid)
    {
        $user = self::with([
            'cash'=>function($q){
                $q->where([
                    'status'=>1,
                    'order_type'=>0,

                ]);
            }
        ])
            ->with('order')
            ->where('id', $uid)
            ->find();
        return $user;
    }
}
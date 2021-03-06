<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 13:23
 */

namespace app\wx\admin;


use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\wx\model\WxOrder;
use think\Db;

class Order extends Admin
{
    public function index()
    {
        $map=$this->getMap();
//        $order=WxOrder::with('user')->where($map)->order('create_time desc')->select()->toArray();
//        dump($order);
        $order=Db::name('wx_order')->alias('w')
            ->join('wx_user  ','wx_user.id=w.uid')
            ->where($map)
            ->order('w.create_time desc')
            ->field('w.id,w.order_no,mobile,openid,w.status,w.pay_type,price,w.time,w.create_time,w.end_time,lnumlist,wx_user.id as uid')
            ->paginate();
//        dump($order);
        return  ZBuilder::make('table')
            ->setTableName('wx_order')
            ->setSearch(['order_no' => '订单编号'], '', '', true) // 设置搜索框
            ->addColumns([
                ['id','ID'],
                ['order_no','订单编号','text'],
                ['uid','用户ID','text'],
                ['mobile','手机号','text'],
                ['openid','openid','text'],
                ['status','计费方式','status','',['待付款', '已付款']],
                ['pay_type','计费方式','status','',['押金', '月卡','混合']],
//                ['status','状态','status','',['逃单', '正常']],
                ['create_time','开始时间','datetime','','Y/m/d H:i:s'],
                ['end_time','结束时间','datetime','','Y/m/d H:i:s'],
                ['time','消费时间','text'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButtons(['delete'])
            ->addRightButtons(['delete'])
            ->setRowList($order)
            ->fetch();
    }
}
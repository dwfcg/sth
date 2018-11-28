<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 18:02
 */

namespace app\wx\service;

use app\lib\exception\OrderException;
use app\lib\exception\ParameterException;
use app\lib\exception\TokenException;
use app\user\service\Token;
use app\wx\home\Lock;
use app\wx\model\WxCash;
use app\wx\model\WxLock;
use app\wx\model\WxUser;
use think\Db;
use think\response\Json;

class Order
{
    public function checkOrder($order_no)
    {
        $order = WxCash::where('order_no', $order_no)->find();
        if (!$order) {
            throw new OrderException();
        }
        if (!Token::isValidOperate($order->uid)) {
            throw new TokenException(
                [
                    'msg' => '订单与用户不匹配',
                    'errorCode' => 10003
                ]);
        }
        return true;

    }

    public function time2string($second)
    {
        $hour = floor($second / 3600);
        $second = $second % 3600;//除去整小时之后剩余的时间
        $minute = floor($second / 60);
//        dump($hour) ;
        if ($minute > 30) {
            $hour += 1;
        } else {
            $hour += 0.5;
        }
        //返回字符串
//            dump($hour) ;
        return $hour;
    }

    public function endOrder($orderData)
    {
        $configData = Db::name('wx_config')->find(1);
        $update['end_time'] = time();
        $update['time'] = $this->time2string(time() - $orderData['create_time']);
        $userModel = new WxUser();
        $userData = $userModel->find($orderData['uid']);
        if ($userData['level'] == 0) {
            $update['pay_type'] = 0;
            $update['price'] = $update['time'] * $configData['hour'];
        } elseif ($userData['level'] == 1) {
            if ($userData['card_end_time'] > time()) {
                if ($userData['time'] > $update['time']) {
                    $update['pay_type'] = 1;
                    $update['price'] = 0;
                } else {
                    $update['pay_type'] = 2;
                    $update['price'] = ($update['time'] - $userData['time'])*$configData['hour'];
                }
            }else{
                $update['pay_type'] = 0;
                $update['price'] = $update['time'] * $configData['hour'];
            }
        }
        return $update;

    }
    //订单前的检验
    public function checkLock($lnumlist)
    {
        $lock=new Lock();
//        dump($lock->isOnline($lnumlist));
        if(!$lock->isOnline($lnumlist)){
//            $returnData=[
//                'msg'=>'锁网络出现问题，联系工作人员',
//                'code'=>'1004',
//            ];
//            return   Json::create($returnData);
            throw new ParameterException([
                'msg'=>'锁网络出现问题，联系工作人员',
                'errorCode'=>1004,
            ]);
        }
        $re=$lock->lockInfo($lnumlist);
        if(!$re){
//            $returnData=[
//                'msg'=>'服务器报错',
//                'code'=>'500',
//            ];
//            return   Json::create($returnData);
            throw new ParameterException([
                'msg'=>'服务器报错',
                'errorCode'=>500
            ]);
        }else{
            if($re['state']==0||$re['state']==2){
//                $returnData=[
//                    'msg'=>'有人使用中或锁损坏',
//                    'code'=>'1005',
//                ];
//                return   Json::create($returnData);
                throw new ParameterException([
                    'msg'=>'有人使用中',
                    'errorCode'=>1005
                ]);
            }elseif ($re['state']==8){
//                $returnData=[
//                    'msg'=>'异常，请通知管理人员',
//                    'code'=>'1006',
//                ];
//                return   Json::create($returnData);
                throw new ParameterException([
                    'msg'=>'异常，请通知管理人员',
                    'errorCode'=>1006
                ]);

            }elseif ($re['state']==9){
//                $returnData=[
//                    'msg'=>'未知原因',
//                    'code'=>'1007',
//                ];
//                return   Json::create($returnData);
                throw new ParameterException([
                    'msg'=>'未知原因',
                    'errorCode'=>1007
                ]);
            }elseif($re['state']==1){
                $model=new WxLock();
                $lockInfo=$model->where('lnumlist',$lnumlist)->find();
                if($lockInfo['status']==0){
                    $re=Db::name('wx_lock')->where('lnumlist',$lockInfo['lnumlist'])->update(['status'=>1]);
                    if($re){
                        return  true;
                    }else{
                        throw new ParameterException([
                            'msg'=>'未知原因',
                            'errorCode'=>10056
                        ]);
                    }

                }elseif($lockInfo['status']==1){
                    throw new ParameterException([
                        'msg'=>'有人使用',
                        'errorCode'=>1008
                    ]);
                }elseif($lockInfo['status']==2){
                    throw new ParameterException([
                        'msg'=>'该锁已损坏，通知管理人员',
                        'errorCode'=>1009
                    ]);
                }
            }
        }
    }
}
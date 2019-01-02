<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 17:31
 */

namespace app\wx\home;


use app\index\controller\Index;
use app\lib\exception\ParameterException;
use app\user\service\Token;
use app\wx\model\WxCash;
use app\wx\model\WxConfig;
use app\wx\model\WxLock;
use app\wx\model\WxOrder;
use app\wx\model\WxUser;
use app\wx\validate\Order as OrderValidate;
use app\wx\service\Order as OrderService;
use think\Db;
use think\response\Json;

class Order extends Index
{
//    订单号
    public static function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;
    }
//创建订单
    public function cashCreateOrder($data, $uid)
    {
        //创建订单
        $cashModel = new  WxCash();
        $cashOrder = [
            'cash' => $data['cash'],
            'order_type' => $data['level'],
            'uid' => $uid,
            'order_no' => self::makeOrderNo(),
        ];
        $cashModel->data($cashOrder);
        $cashModel->save();
        return $cashModel->order_no;
    }
//    押金或者月卡订单支付
    public function orderPay()
    {
        $orderValidate=new OrderValidate();
//        $orderValidate->goCheck();
        $data=$orderValidate->getDataByRule(input('post.'));
//        dump($data);
        $orderService=new OrderService();
        $orderService->checkOrder($data['order_no']);
        $pay=new Pay();
        //调用pay得到支付参数
        $rel=$pay->cashpay($data['order_no']);
        return  Json::create($rel);

    }
    //预消费订单的生成
   public function place()
   {
       $lockController=new Lock();
       $validate=new OrderValidate();
//       $validate->goCheck();
       $uid=Token::getCurrentUid();
       $data=$validate->getDataByRule(input('post.'));
//       $uid=5;
       $userData=WxUser::get($uid);
//       dump($userData);die();
       if(!$userData->status){
           $returnData=[
               'msg'=>'该账号有问题，联系管理员',
               'code'=>'1010',
           ];
           return   Json::create($returnData);
//           throw new ParameterException([
//               'msg'=>'该账号有问题，联系管理员',
//               'errorCode'=>1010
//           ]);
       }
       $re=WxOrder::where(['uid'=>$uid,'status'=>0])->find();

       if($re){
           if($re['lnumlist']==$data['lnumlist']){
               $lockpwd=WxLock::where('lnumlist',$data['lnumlist'])->find();
               $lockController->unlock($re['lnumlist'],$lockpwd->pwd);
               $returnData=[
                   'msg'=>'锁已开',
                   'code'=>'1011',
               ];
               return   Json::create($returnData);
//               throw new ParameterException([
//                   'msg'=>'锁已开',
//                   'errorCode'=>1011
//               ]);
           }else{
               $returnData=[
                   'msg'=>'存在未支付订单',
                   'code'=>'1012',
               ];
               return   Json::create($returnData);
//               throw new ParameterException([
//                   'msg'=>'存在未支付订单',
//                   'errorCode'=>1012
//               ]);
           }

       }
       $orderService=new OrderService();
       $orderService->checkLock($data['lnumlist']);

       $lockController->updateLock($data['lnumlist']);
       $lockpwd=WxLock::where('lnumlist',$data['lnumlist'])->find();
       $lockController->unlock($data['lnumlist'],$lockpwd->pwd);
       //TODO:推送  断电

//       dump($re);
       $data=[
           'uid'=>$uid,
           'lnumlist'=>$data['lnumlist'],
           'order_no'=>self::makeOrderNo(),
       ];
       $orderModel=new WxOrder();
       $orderModel->data($data)->save();
       $order_no=$orderModel->order_no;
       $returnData=[
           'lnumlist'=>$data['lnumlist'],
           'order_no'=>$data['order_no'],
       ];
       return  Json::create(['order_no'=>$returnData]);
   }
   //结束订单的生成
   public function endOrder()
   {
       $validate=new OrderValidate();
       $validate->goCheck();
       $data=$validate->getDataByRule(input('post.'));
       $order_no=$data['order_no'];
       $orderData=WxOrder::where('order_no',$order_no)->find();
       if($orderData['status']==1){
           throw new ParameterException([
               'msg'=>'该订单已经支付完成'
           ]);
       }
       $orderService=new OrderService();
       $updateData=$orderService->endOrder($orderData);
       $rel=WxOrder::update($updateData,['order_no'=>$order_no]);
       $orderNewData=WxOrder::where('order_no',$order_no)->find();
      return    Json::create(['order_no'=>$orderNewData]);

   }
   public function pay()
   {
       $validate=new OrderValidate();
//       $validate->goCheck();
       $data=$validate->getDataByRule(input('post.'));
       $order_no=$data['order_no'];
       $orderData=WxOrder::with('user')->where('order_no',$order_no)->find();
//       dump($orderData->toArray());
       //调用支付
       $pay=new Pay();
       //调用pay得到支付参数
       $rel=$pay->wxOrder($orderData->toArray());
       return  Json::create($rel);

   }
//   获取订单信息
   public function getOrderInfo()
   {
       $data=input('post.');
//       dump($data['order_no']);
       $orderInfo=WxOrder::get(['order_no'=>$data['order_no']]);
//       dump($orderInfo);
       return  Json::create($orderInfo);
   }
   public function getUserOrder()
   {
       $uid=Token::getCurrentUid();
//       $uid=22;
       $data=WxOrder::all(['uid'=>$uid]);
       return  Json::create($data);
   }

}
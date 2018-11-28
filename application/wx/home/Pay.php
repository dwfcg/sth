<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 16:15
 */

namespace app\wx\home;
use app\wx\model\WxCash;
use app\wx\model\WxConfig;
use app\wx\model\WxLock;
use app\wx\model\WxOrder;
use think\Db;
use think\response\Json;
use Yansongda\Pay\Pay   as Paycontroller;
use Yansongda\Pay\Log;

use app\index\controller\Index;

class Pay   extends Index
{
    protected $config = [
        'app_id' => 'wxab9df49d05550d41', // 公众号 APPID
        'miniapp_id' => 'wx4610885af53f6d69', // 小程序 APPID
        'mch_id' => '1519468901',
        'key' => 'e6d82af59ca1c47735bd61ed561d0ba4',
        'notify_url' => '',
        'cert_client' => './cert/apiclient_cert.pem', // optional，退款等情况时用到
        'cert_key' => './cert/apiclient_key.pem',// optional，退款等情况时用到
        'log' => [ // optional
            'file' => './logs/wechat.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
    ];

    //月卡或者押金的支付
    public function cashpay($order_no)
    {
        $orderInfo=WxCash::with('user')->where('order_no',$order_no)->select()->toArray();
//        dump($orderInfo);
        $this->config['notify_url']='https://chashi.youacloud.com/index.php/wx/pay/cashnotify_url';
        $order = [
            'out_trade_no' =>$orderInfo[0]['order_no'],
            'body' => '费用缴纳',
            'total_fee' =>$orderInfo[0]['cash']*100,
            'openid' => $orderInfo[0]['user']['openid'],
        ];
        $pay = Paycontroller::wechat($this->config);
        $result = $pay->miniapp($order);

       return   $result->toArray();
    }

    /**
     * 月卡押金的回调
     */
    public function cashnotify_url()
    {
        $this->config['notify_url']='https://chashi.youacloud.com/index.php/wx/pay/cashnotify_url';
        $wxpay =  Paycontroller::wechat($this->config);
        Db::startTrans();
        try{
            $data = $wxpay->verify(); // 是的，验签就这么简单！
            $out_trade_no = $data -> out_trade_no;
            $data['trade_no']=  $data -> trade_no;
            $data['status']=  1;
            $orderCashModel=new WxCash();
            $orderInfo=$orderCashModel->get(['order_no'=>$out_trade_no]);
            if(!$orderInfo->status){
                $orderCashModel->where(['order_no'=>$out_trade_no])->update($data);
                if($orderInfo->order_type){
                    $config=new WxConfig();
                    $configData=$config->find('1');
                    $update=[
                        'level'=>1,
                        'start_time'=>time(),
                        'time'=>$configData->time,
                        'card_end_time'=>strtotime('+'.'30'.'days'),
                    ];
                    Db::name('user')->where(['id'=>$orderInfo->uid])->update($update);
                }else{
                    Db::name('user')->where(['id'=>$orderInfo->uid])->update(['level'=>0]);
                }

            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
        }
        return $wxpay->success()->send();// laravel 框架中请直接 `return $pay->success()`
    }

    /**
     * 订单的支付
     */
    public function wxOrder($orderInfo)
    {
        $this->config['notify_url']='https://chashi.youacloud.com/index.php/wx/pay/wxOrderNotifyUrl';
        $order = [
            'out_trade_no' =>$orderInfo['order_no'],
            'body' => '费用缴纳',
            'total_fee' =>$orderInfo['price']*100,
            'openid' => $orderInfo['user']['openid'],
        ];
        $pay = Paycontroller::wechat($this->config);
        $result = $pay->miniapp($order);

        return   $result->toArray();
    }
    public function wxOrderNotifyUrl()
    {
        $this->config['notify_url']='https://chashi.youacloud.com/index.php/wx/pay/wxOrderNotifyUrl';
        $wxpay =  Paycontroller::wechat($this->config);
        Db::startTrans();
        try{
            $data = $wxpay->verify(); // 是的，验签就这么简单！
            $out_trade_no = $data -> out_trade_no;
            $data['trade_no']=  $data -> trade_no;
            $data['status']=  1;
            $orderOrderModel=new WxOrder();
            $orderInfo=$orderOrderModel->get(['order_no'=>$out_trade_no]);
            if(!$orderInfo->status){
                $orderOrderModel->where(['order_no'=>$out_trade_no])->update($data);
            }
            WxLock::update(['status'=>0],['lnumlist'=>$orderInfo['lnumlist']]);
            Db::commit();
            //TODO:推送 断电
        } catch (Exception $e) {
            Db::rollback();
        }
        return $wxpay->success()->send();// laravel 框架中请直接 `return $pay->success()`
    }
    public function time2string($second){
        $day = floor($second/(3600*24));
        $second = $second%(3600*24);//除去整天之后剩余的时间
        $hour = floor($second/3600);
        $second = $second%3600;//除去整小时之后剩余的时间
        $minute = floor($second/60);
        $second = $second%60;//除去整分钟之后剩余的时间
//返回字符串
        return $day.'天'.$hour.'小时'.$minute.'分'.$second.'秒';
    }
    public function refund(){

        $this->config['notify_url']='https://chashi.youacloud.com/index.php/wx/pay/refundNotifyUrl';
        $wxpay =  Paycontroller::wechat($this->config);
        $order_no=input('order_no');
        $cashModel=new WxCash();
        $cashOrder=$cashModel->get(['order_no'=>$order_no]);
        //TODO:退款
//        $order = [
//            'out_trade_no' => $cashOrder->trade_no,
//            'out_refund_no' => time(),
//            'total_fee' =>$cashOrder->cash*100,
//            'refund_fee' => '1',
//            'refund_desc' => '测试退款haha',
//        ];

//        $result = $wxpay->refund($order);
    }

//    public function notify()
//    {
//        $pay = Pay::wechat($this->config);
//
//        try{
//            $data = $pay->verify(); // 是的，验签就这么简单！
//
//            Log::debug('Wechat notify', $data->all());
//        } catch (Exception $e) {
//            // $e->getMessage();
//        }
//
//        return $pay->success()->send();// laravel 框架中请直接 `return $pay->success()`
//    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 16:15
 */

namespace app\wx\home;
use app\wx\model\WxCash;
use Yansongda\Pay\Pay   as Paycontroller;
use Yansongda\Pay\Log;

use app\index\controller\Index;

class Pay   extends Index
{
    protected $config = [
//        'appid' => 'wx4610885af53f6d69', // APP APPID
//        'app_id' => 'wxb3fxxxxxxxxxxx', // 公众号 APPID
        'miniapp_id' => 'wx4610885af53f6d69', // 小程序 APPID
        'mch_id' => '1493283802',
        'key' => '6fd15a3a419fbd2c252b4023c11b9900',
        'notify_url' => 'https://chashi.youacloud.com/index.php/wx/pay/index',
        'cert_client' => './cert/apiclient_cert.pem', // optional，退款等情况时用到
        'cert_key' => './cert/apiclient_key.pem',// optional，退款等情况时用到
        'log' => [ // optional
            'file' => './logs/wechat.log',
            'level' => 'debug', // 建议生产环境等级调整为 info，开发环境为 debug
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
    public function index($order_no)
    {
        $orderInfo=WxCash::where('order_no',$order_no)->select()->toArray();
        $order = [
        'out_trade_no' => time(),
        'body' => 'subject-测试',
        'total_fee' => '1',
        'openid' => 'oGkfT5Pe-HoSZWrp2gi3q1f2XPFY',
        ];

//        $result = $wechat->miniapp($order);
        $pay = Paycontroller::wechat($this->config)->scan($order)->send();
        dump($pay);

        // $pay->appId
        // $pay->timeStamp
        // $pay->nonceStr
        // $pay->package
        // $pay->signType
    }
    public function demo()
    {
        $time=time();
        dump($this->time2string($time-1539755879));
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
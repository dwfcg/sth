<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 河源市卓锐科技有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

namespace plugins\DySms\controller;

require_once(dirname(dirname(__FILE__))."/sdk/vendor/autoload.php");
use app\common\controller\Common;
use plugins\DySms\model\DySms as SmsModel;
use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\SendBatchSmsRequest;

// 加载区域结点配置
Config::load();

/**
 * sms控制器
 * @package plugins\DySms\controller
 * @author 小乌 <82950492@qq.com>
 */
class DySms extends Common
{
    static $acsClient = null;

    /**
     * 取得AcsClient
     * @return DefaultAcsClient
     */
    public static function getAcsClient() {
        // 产品名称:云通信流量服务API产品,开发者无需替换
        $product = "Dysmsapi";

        // 产品域名,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";

        // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
        // AccessKeyId
        $accessKeyId = plugin_config('DySms.appkey');

        // AccessKeySecret
        $accessKeySecret = plugin_config('DySms.secret');

        // 暂时不支持多Region
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";

        if(static::$acsClient == null) {
            // 初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }

    /**
     * 发送短信
     * @param string $rec_num 短信接收号码
     * @param array $sms_param 短信模板变量
     * @param string $sms_template 短信模板名称
     * @param string $sms_extend 上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
     *      公共回传参数，在“消息返回”中会透传回该参数；举例：用户可以传入自己下级的会员ID，
     *      在消息返回时，该会员ID会包含在内，用户可以根据该会员ID识别是哪位会员使用了你的应用
     * @return array
     *
     * 示例：
     * $result = plugin_action('DySms/DySms/send', ['手机号码', [模板变量], '模板名称']);
     * if($result['code']){
     *     $this->error('发送失败，错误代码：'. $result['code']. ' 错误信息：'. $result['msg']);
     * } else {
     *     $this->success('发送成功');
     * }
     */
    public static function send($rec_num = '', $sms_param = [], $sms_template = '', $sms_extend = '123456') {
        // 插件配置参数
        $config = plugin_config('DySms');
        if ($config['status'] != '1') {
            return ['code' => 1, 'msg' => '短信功能已关闭'];
        }
        if ($config['appkey'] == '') {
            return ['code' => 2, 'msg' => '请填写APPKEY'];
        }
        if ($config['secret'] == '') {
            return ['code' => 3, 'msg' => '请填写SECRET'];
        }
        if ($rec_num == '') {
            return ['code' => 4, 'msg' => '请填写短信接收号码'];
        }
        if ($sms_template == '') {
            return ['code' => 6, 'msg' => '没有设置短信模板'];
        }

        $template = SmsModel::getTemplate($sms_template);
        if (!$template) {
            return ['code' => 7, 'msg' => '找不到短信模板'];
        }

        // 模板参数
        if ($template['status'] == '0') {
            return ['code' => 8, 'msg' => '短信模板已禁用'];
        }
        if ($template['code'] == '') {
            return ['code' => 9, 'msg' => '请设置模板ID'];
        }
        if ($template['sign_name'] == '') {
            return ['code' => 10, 'msg' => '请设置短信签名'];
        }

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        // 必填，设置短信接收号码
        $request->setPhoneNumbers($rec_num);

        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName($template['sign_name']);

        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode($template['code']);

        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        if (!empty($sms_param)) {
            $request->setTemplateParam(json_encode($sms_param, JSON_UNESCAPED_UNICODE));
        }

        // 可选，设置流水号
//        $request->setOutId("yourOutId");

        // 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
        $request->setSmsUpExtendCode($sms_extend);

        // 发起访问请求
        $acsResponse = static::getAcsClient()->getAcsResponse($request);

        if (isset($acsResponse->code) && $acsResponse->code != 0) {
            // 发送失败
            $msg = isset($acsResponse->sub_msg) ? $acsResponse->sub_msg : $acsResponse->msg;
            return ['code' => $acsResponse->code, 'msg' => $msg];
        } else {
            // 发送成功
            return ['code' => 0, 'msg' => '发送成功'];
        }
    }

    /**
     * 批量发送短信
     * @param array $rec_nums 短信接收号码
     * @param array $sms_params  短信模板变量
     * @param string $sms_template 短信模板名称
     * @return array
     */
    public function sendBatch($rec_nums = [], $sms_params = [], $sms_template = '')
    {
        // 插件配置参数
        $config = plugin_config('DySms');
        if ($config['status'] != '1') {
            return ['code' => 1, 'msg' => '短信功能已关闭'];
        }
        if ($config['appkey'] == '') {
            return ['code' => 2, 'msg' => '请填写APPKEY'];
        }
        if ($config['secret'] == '') {
            return ['code' => 3, 'msg' => '请填写SECRET'];
        }
        if (empty($rec_nums)) {
            return ['code' => 4, 'msg' => '请填写短信接收号码'];
        }
        if (count($rec_nums) > 100) {
            return ['code' => 5, 'msg' => '批量上限为100个手机号码'];
        }
        if (empty($sms_params)) {
            return ['code' => 6, 'msg' => '请填写短信模板变量'];
        }
        if ($sms_template == '') {
            return ['code' => 7, 'msg' => '没有设置短信模板'];
        }

        $template = SmsModel::getTemplate($sms_template);
        if (!$template) {
            return ['code' => 8, 'msg' => '找不到短信模板'];
        }

        // 模板参数
        if ($template['status'] == '0') {
            return ['code' => 9, 'msg' => '短信模板已禁用'];
        }
        if ($template['code'] == '') {
            return ['code' => 10, 'msg' => '请设置模板ID'];
        }
        if ($template['sign_name'] == '') {
            return ['code' => 11, 'msg' => '请设置短信签名'];
        }

        $sign_name = [];
        foreach ($rec_nums as $value) {
            $sign_name[] = $template['sign_name'];
        }

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendBatchSmsRequest();

        // 必填:待发送手机号。支持JSON格式的批量调用，批量上限为100个手机号码,批量调用相对于单条调用及时性稍有延迟,验证码类型的短信推荐使用单条调用的方式
        $request->setPhoneNumberJson(json_encode($rec_nums, JSON_UNESCAPED_UNICODE));

        // 必填:短信签名-支持不同的号码发送不同的短信签名
        $request->setSignNameJson(json_encode($sign_name, JSON_UNESCAPED_UNICODE));

        // 必填:短信模板-可在短信控制台中找到
        $request->setTemplateCode($template['code']);

        // 必填:模板中的变量替换JSON串,如模板内容为"亲爱的${name},您的验证码为${code}"时,此处的值为
        // 友情提示:如果JSON中需要带换行符,请参照标准的JSON协议对换行符的要求,比如短信内容中包含\r\n的情况在JSON中需要表示成\\r\\n,否则会导致JSON在服务端解析失败
        $request->setTemplateParamJson(json_encode($sms_params, JSON_UNESCAPED_UNICODE));

        // 可选-上行短信扩展码(扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段)
        // $request->setSmsUpExtendCodeJson("[\"90997\",\"90998\"]");

        // 发起访问请求
        $acsResponse = static::getAcsClient()->getAcsResponse($request);

        if (isset($acsResponse->code) && $acsResponse->code != 0) {
            // 发送失败
            $msg = isset($acsResponse->sub_msg) ? $acsResponse->sub_msg : $acsResponse->msg;
            return ['code' => $acsResponse->code, 'msg' => $msg];
        } else {
            // 发送成功
            return ['code' => 0, 'msg' => '发送成功'];
        }
    }
}
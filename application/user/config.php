<?php
//配置文件
return [
    'wxpay_config'=>array(
        // 小程序app_id
        'app_id' => 'wxaa0458ae1b3f8f36',
        // 小程序app_secret
        'app_secret' => '05458b34c839c4ba4e01402fd7862a77',
        'mch_id'=>'1493283802',
        // 'key'=>'Mhjfdsklgmkls2549816515555572653',
        'key'=>'n0alu1cnrq8bdus6jcpnnhkig421suz6',
        // 微信使用code换取用户openid及session_key的url地址
        'login_url' => "https://api.weixin.qq.com/sns/jscode2session?" .
            "appid=%s&secret=%s&js_code=%s&grant_type=authorization_code",

        // 微信获取access_token的url地址
        'access_token_url' => "https://api.weixin.qq.com/cgi-bin/token?" .
            "grant_type=client_credential&appid=%s&secret=%s",
//        'notify_url'=>'http://yusuzhou.youacloud.com/index.php/shop/zhifu/weixin_notify_url'
    ),
];
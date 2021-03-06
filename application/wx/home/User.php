<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 14:03
 */

namespace app\wx\home;


use app\index\controller\Home;
use app\lib\exception\ParameterException;
use app\user\service\Token;
use app\wx\model\WxCash;
use app\wx\model\WxConfig;
use app\wx\model\WxUser;
use app\wx\validate\Cash;
use app\wx\validate\User as UserValidate;
use think\Db;
use think\response\Json;

class User  extends Home
{
    public function editUser()
    {
        $validate=new UserValidate();
        $validate->goCheck();
        $data=input('post.');
        $data=$validate->getDataByRule($data);
        $rel=$this->validate($data,'user');
        if(!$rel)
        {
            throw new ParameterException([
                'msg'=>$rel
            ]);
        }
        if(Sms::checkCode()){
//            echo 11;
            $uid=Token::getCurrentUid();
//            $uid=4;
            $user=WxUser::get(['id'=>$uid]);
            $config=WxConfig::get(['id',1]);
            if($user['update_time']+$config->date*24*3600>time()){
                throw new ParameterException([
                    'msg'=>'更换频率过高'
                ]);
            }
            $data=WxUser::update($data,['id'=>$uid]);
            return Json::create(['code'=>1]);
        }else{
            $returnData=[
                'msg'=>'code填写不正确',
                'code'=>'1023',
            ];
            return   Json::create($returnData);
        }

    }
    public function getConfig()
    {
        $config=WxConfig::all();
        return Json::create($config);
    }
    //预订单押金或月卡
    public function addcash()
    {
        $validata=new Cash();
//        $validata->goCheck();
        $data=$validata->getDataByRule(input('post.'));
        $uid=Token::getCurrentUid();
//        $uid=4;
        $cashorder=new Order();
        $order_no=$cashorder->cashCreateOrder($data,$uid);
        return  Json::create(['order_no'=>$order_no]);

    }
    public function getUserInfo()
    {
        $validate=new UserValidate();
//        $validate->goCheck();
        $uid=Token::getCurrentUid();
//        $uid=4;
        $data=WxUser::getByID($uid);
        $data=$data->toArray();
        foreach ($data['order'] as &$v){
            $v['create_time']=date('Y-m-d h:i:s',$v['create_time']);
        }
        return Json::create($data);

    }
//    押金或月卡的信息
    public function cashOrCard()
    {
        $validate=new UserValidate();
//        $validate->goCheck();
        $uid=Token::getCurrentUid();
//        $uid=4;
        $data=$validate->getDataByRule(input('post.'));
        $cashModel=new WxCash();
        if($data['order_type']==1){
            $data=$cashModel->card($uid);
        }else{
            $data=$cashModel->cash($uid);
        }
        return Json::create($data);
    }



}
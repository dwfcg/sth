<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/8
 * Time: 16:49
 */

namespace app\wx\admin;


use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\wx\model\WxUser;

class User  extends Admin
{
    public function index()
    {
        $map=$this->getMap();
//        dump($map);
        $user=WxUser::where($map)->select()->toArray();

        return  ZBuilder::make('table')
            ->setTableName('wx_user')
            ->setSearch(['openid' => 'openid','mobile'=>'手机号'], '', '', true) // 设置搜索框
            ->addColumns([
                ['id','ID'],
//                ['nickname','昵称','text'],
                ['mobile','手机号','text'],
                ['openid','openid','text'],
                ['level','计费方式','status','',['小时', '月卡']],
                ['status','状态','status','',['逃单', '正常']],
                ['create_time','创建时间','datetime','','Y/m/d H:i:s'],
                ['right_button', '操作', 'btn']
            ])
            ->addRightButtons(['edit'])
            ->setRowList($user)
            ->fetch();
    }
    public function edit($id = '')
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $advert = WxUser::update($data, ['id' => $data['id']]);
            if ($advert) {
                $this->success('修改成功', 'index');
            } else {
                $this->error('修改失败');
            }
        }
        $user = WxUser::get($id)->toArray();
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['radio', 'status', '用户状态', '请输入',['逃单','正常']],

            ])
            ->setFormData($user)
            ->fetch();
    }
}
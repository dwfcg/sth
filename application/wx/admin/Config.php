<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 11:11
 */

namespace app\wx\admin;


use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\wx\model\WxConfig;

class Config extends Admin
{
    public function index()
    {
        $map = $this->getMap();
        $cabin = WxConfig::where($map)->select()->toArray();
        return ZBuilder::make('table')
            ->setTableName('wx_config')
            ->addColumns([
                ['month_card', '月卡金额', 'text.edit'],
                ['hour', '小时金额', 'text.edit'],
                ['cash', '押金', 'text.edit'],
                ['time', '月卡小时数', 'text.edit'],
                ['date', '更换手机号:天', 'text.edit'],
            ])
            ->setRowList($cabin)
            ->fetch();
    }

    public function edit($id = '')
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $result = $this->validate($data, 'cabin');
            if (true !== $result) {
                // 验证失败 输出错误信息
                return $this->error($result);
            }
            $advert = WxCabin::update($data, ['id' => $data['id']]);
            if ($advert) {
                $this->success('修改成功', 'index');
            } else {
                $this->error('修改失败');
            }
        }
        $cabin = WxCabin::get($id)->toArray();
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['text', 'name', '名称', '请输入'],
                ['text', 'cnumlist', '编号', '请输入'],
                ['linkages', 'address', '修改收货地区', '', 'address', 4, ''],
                ['text', 'addressinfo', '详细地址', '请输入'],
            ])
            ->setFormData($cabin)
            ->fetch();
    }
}
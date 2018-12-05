<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 10:21
 */

namespace app\wx\admin;


use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\wx\model\WxCabin;

class Cabin     extends Admin
{
    public function index()
    {
        $map=$this->getMap();
        $cabin=WxCabin::where($map)->paginate();
        return  ZBuilder::make('table')
            ->setTableName('wx_cabin')
            ->setSearch(['name' => '昵称','cnumlist'=>'编号'], '', '', true) // 设置搜索框
            ->addColumns([
                ['id','ID'],
                ['cnumlist','编号','text'],
                ['name','昵称','text.edit'],
                ['status','状态','status','',['空闲', '使用中']],
                ['create_time','创建时间','datetime','','Y/m/d H:i:s'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButtons(['add','delete'])
            ->addRightButtons(['edit','delete'])
            ->setRowList($cabin)
            ->fetch();
    }
    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();
            $result=$this->validate($data,'cabin');
            if(true !== $result){
                // 验证失败 输出错误信息
                return  $this->error($result);
            }
            $advert=WxCabin::create($data);
            if ($advert) {
                $this->success('新增成功', 'index');
            } else {
                $this->error('新增失败');
            }
        }
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden','id'],
                ['text', 'name', '名称','请输入'],
                ['text', 'cnumlist', '编号','请输入'],
                ['linkages', 'address', '收货地区', '', 'address', 4, ''],
                ['text', 'addressinfo', '详细地址','请输入'],
            ])
            ->fetch();
    }
    public function edit($id = '')
    {
        if($this->request->isPost())
        {
            $data=$this->request->post();
            $result=$this->validate($data,'cabin');
            if(true !== $result){
                // 验证失败 输出错误信息
                return  $this->error($result);
            }
            $advert=WxCabin::update($data,['id'=>$data['id']]);
            if ($advert) {
                $this->success('修改成功', 'index');
            } else {
                $this->error('修改失败');
            }
        }
        $cabin=WxCabin::get($id)->toArray();
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden','id'],
//                ['hidden','cnumlist'],
                ['text', 'name', '名称','请输入'],
                ['text', 'cnumlist', '编号'],
                ['linkages', 'address', '修改收货地区', '', 'address', 4, ''],
                ['text', 'addressinfo', '详细地址','请输入'],
            ])
            ->setFormData($cabin)
            ->fetch();
    }
}
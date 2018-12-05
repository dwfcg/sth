<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 11:40
 */

namespace app\wx\admin;


use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\wx\home\ImageUpload;
use app\wx\home\Wxtoken;
use app\wx\model\WxCabin;
use app\wx\model\WxLock;
use think\Cache;

class Lock  extends Admin
{
    public function index()
    {
        $map=$this->getMap();
        $cabin=WxLock::where($map)->paginate();

        return  ZBuilder::make('table')
            ->setTableName('wx_lock')
            ->setSearch(['lnumlist'=>'编号'], '', '', true) // 设置搜索框
            ->addColumns([
                ['id','ID'],
//                ['title', '商品标题','link',url('http://yusuzhou.youacloud.com/index.php/shop/goods/getUrl',['id'=>'__id__'])],
                ['lnumlist','锁编号','text'],
                ['cnumlist','舱体编号','text'],
                ['status','状态','status','',['闲置', '使用中','损坏']],
                ['create_time','创建时间','datetime','','Y/m/d H:i:s'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButtons(['add','delete'])
            ->addRightButtons(['edit','delete'])
            ->addRightButton('custom',['href' => url('access', ['lnumlist' => '__lnumlist__']),'title'=>' 下载二维码']) // 添加授权按钮
            ->setRowList($cabin)
            ->fetch();
    }
    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();
            $result=$this->validate($data,'lock');
            if(true !== $result){
                // 验证失败 输出错误信息
                return  $this->error($result);
            }
            $re=WxCabin::get(['cnumlist'=>$data['cnumlist']]);
            if(!$re) {
                $this->error('请确定舱体编号');
            }
            $advert=WxLock::create($data);
            if ($advert) {
                $this->success('新增成功', 'index');
            } else {
                $this->error('新增失败');
            }
        }
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden','id'],
                ['text', 'lnumlist', '编号'],
                ['text', 'cnumlist', '舱体ID'],
//                ['ueditor','content','商品内容','<span class="text-danger">请直接上传图片就行,不要对图片进行过多操作</span>'],
            ])
            ->fetch();
    }
    public function edit($id = '')
    {
        if($this->request->isPost())
        {
            $data=$this->request->post();
            $result=$this->validate($data,'lock');
            if(true !== $result){
                // 验证失败 输出错误信息
                return  $this->error($result);
            }
            $re=WxCabin::get(['cnumlist'=>$data['cnumlist']]);
            if($re) {
                $advert=WxLock::update($data,['id'=>$data['id']]);
                if ($advert) {
                    $this->success('修改成功', 'index');
                } else {
                    $this->error('修改失败');
                }
            }else{
                $this->error('请确定舱体编号是否存在');
            }

        }
        $cabin=WxLock::get($id)->toArray();
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden','id'],
                ['text', 'lnumlist', '编号','请输入'],
                ['text', 'cnumlist', '编号','请输入'],
            ])
            ->setFormData($cabin)
            ->fetch();
    }
    public function access()
    {
        $lnumlist = input('lnumlist');
        $wxtokenController = new Wxtoken();
        $wxtoken = $wxtokenController->refreshWxToken();
//        dump($wxtoken);
////        POST https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=ACCESS_TOKEN
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $wxtoken;
        $array = [
            'scene' => $lnumlist,
//            'page'=>"pages/index/index"
        ];
        $param = json_encode(array("scene"=>$lnumlist,"page"=>"pages/index/index","width"=> 150));
        $result = $this->httpRequest( $url, $param,"POST");
        $ret = file_put_contents($lnumlist.'.png', $result, true);
        $fileName = $lnumlist.'.png'; //得到文件名
        header( "Content-Disposition:  attachment;  filename=".$fileName); //告诉浏览器通过附件形式来处理文件
        header('Content-Length: ' . filesize($fileName)); //下载文件大小
        readfile($fileName);  //读取文件内容
    }
    //把请求发送到微信服务器换取二维码
    function httpRequest($url, $data='', $method='GET'){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if($method=='POST')
        {
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data != '')
            {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }

        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public  function binary_to_file($file){
        $content = $GLOBALS['HTTP_RAW_POST_DATA'];  // 需要php.ini设置
        if(empty($content)){
            $content = file_get_contents('php://input');    // 不需要php.ini设置，内存压力小
        }
        $ret = file_put_contents($file, $content, true);
        return $ret;
    }

    // demo




}
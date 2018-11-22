<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/12
 * Time: 13:15
 */

namespace app\wx\home;


use app\index\controller\Home;
use app\index\controller\Index;

class Images    extends Index
{
    //思路改变
    public function demo()
    {
        $path = 'images/';
        if(!file_exists($path))
        {
            if(mkdir($path,0777,true))
            {
                $img ="http://yusuzhouimg.youacloud.com/9dd04230792983b036b3c75181194194.JPG?v=649538" ;
                ob_clean();
                ob_start();
                readfile($img);		//读取图片
                $img = ob_get_contents();	//得到缓冲区中保存的图片
                ob_end_clean();		//清空缓冲区
                $time=time();
                $fp = fopen($path.'a.jpg','w');	//写入图片
                if(fwrite($fp,$img))
                {
                    fclose($fp);
                    echo "图片保存成功";
                }
            }
        }
    }
    public function createImage()
    {
       $imageUpload=new ImageUpload('','');
       $imageUpload->stream2Image();
    }
}
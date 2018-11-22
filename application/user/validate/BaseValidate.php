<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/7
 * Time: 15:57
 */

namespace app\user\validate;


use app\lib\exception\ParameterException;
use think\Request;
use think\Validate;

class BaseValidate  extends Validate
{
    /**
     * 检测所有客户端发来的参数是否符合验证类规则
     * 基类定义了很多自定义验证方法
     * 这些自定义验证方法其实，也可以直接调用
     * @throws ParameterException
     * @return true
//     */
    public function goCheck()
    {
        $request=Request::instance();
        $params=$request->param();
        $params['token']=$request->header('token');
        if(!$this->check($params)){
            $exception=new ParameterException([
                'msg'=>is_array($this->error)?implode(';',$this->error):$this->error,
            ]);
            throw $exception;
        }
        return  true;
    }
    protected function isNotEmpty($value, $rule='', $data='', $field='')
    {
        if (empty($value)) {
            return $field . '不允许为空';
        } else {
            return true;
        }
    }
    /**
     * @param array $arrays 通常传入request.post变量数组
     * @return array 按照规则key过滤后的变量数组
     * @throws ParameterException
     */
    public function getDataByRule($arrays)
    {
        if (array_key_exists('user_id', $arrays) | array_key_exists('uid', $arrays)) {
            // 不允许包含user_id或者uid，防止恶意覆盖user_id外键
            throw new ParameterException([
                'msg' => '参数中包含有非法的参数名user_id或者uid'
            ]);
        }
        $newArray = [];
        foreach ($arrays as $key => $value) {
            $newArray[$key] = $arrays[$key];
        }
        return $newArray;
    }
    //没有使用TP的正则验证，集中在一处方便以后修改
    //不推荐使用正则，因为复用性太差
    //手机号的验证规则
    protected function isMobile($value)
    {
        $rule = '^1(3|4|5|7|8)[0-9]\d{8}$^';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

}
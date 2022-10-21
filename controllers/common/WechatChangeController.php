<?php

namespace app\controllers\common;

use app\controllers\Controller;

/**
 * 微信零钱处理类
 * Class WechatChangeController
 * @package app\controllers\common
 */
class WechatChangeController
{
    public function createNoncestr($length =32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    public function unicode() {
        $str = uniqid(mt_rand(),1);
        $str=sha1($str);
        return md5($str);
    }
   public function arraytoxml($data){
        $str='<xml>';
        foreach($data as $k=>$v) {
            $str.='<'.$k.'>'.$v.'</'.$k.'>';
        }
        $str.='</xml>';
        return $str;
    }
   public function xmltoarray($xml) {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
    }

    public function curl($param="",$url) {
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();                                      //初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);                 //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);           // 增加 HTTP Header（头）里的字段
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch,CURLOPT_SSLCERT,'/home/wwwroot/maiji/runtime/pem/15fd8f2b92f4a0a4d045d7498273ab08'); //这个是证书的位置绝对路径
        curl_setopt($ch,CURLOPT_SSLKEY,'/home/wwwroot/maiji/runtime/pem/64ebd66ac53c9defb1b7946f0b6bda46'); //这个也是证书的位置绝对路径
        $data = curl_exec($ch);                                 //运行curl
        curl_close($ch);
        return $data;
    }

    /**
     *  商家转账api
     * @return \SimpleXMLElement
     */
    public function test()
    {
        $data = [
            'appid'=>'wx76c8ca6b14d702a6',
            'out_batch_no'=>1509288651,
            'batch_name'=>'test',
            'batch_remark'=>'test',
            'total_amount'=>1,
            'total_num'=>1,
            'transfer_detail_list'=>[
                'out_detail_no'=>date('YmdHis').rand(1000, 9999),//商户订单号,
                'transfer_amount'=>1,
                'transfer_remark'=>'test',
                'openid'=>'oJk4Q5SCwC95V9vkVC8zNvNIctqk',
                'user_name'=>'起风了',
            ]

        ];
        echo "<pre>"; var_dump($data);die;
        $xml=$this->arraytoxml($data);

        $url='https://api.mch.weixin.qq.com/v3/transfer/batchess'; //调用接口
        $res=$this->curl($xml,$url);
        $return=$this->xmltoarray($res);


        echo "<pre>";print_r($return); die;
    }


    /*
    $amount 发送的金额（分）目前发送金额不能少于1元
    $re_openid, 发送人的 openid
    $desc  //  企业付款描述信息 (必填)
    $check_name    收款用户姓名 (选填)
    */
   public  function sendMoney(){

//        $total_amount = (100) * $amount;

        $data=array(
            'mch_appid'=>'wx76c8ca6b14d702a6',//商户账号appid
            'mchid'=> 1509288651,//商户号
            'nonce_str'=>$this->createNoncestr(),//随机字符串
            'partner_trade_no'=> date('YmdHis').rand(1000, 9999),//商户订单号
            'openid'=> 'oJk4Q5SCwC95V9vkVC8zNvNIctqk',//$re_openid,//用户openid
            'check_name'=>'NO_CHECK',//校验用户姓名选项,
            're_user_name'=> '刘丹凤',//收款用户姓名
            'amount'=>'1',//金额
            'desc'=> 1232,//企业付款描述信息
            'spbill_create_ip'=> $_SERVER['REMOTE_ADDR'],//\Yii::$app->request->userHostAddress,//Ip地址
        ); ;
        $secrect_key="eric15026916392eric15026916392mj";///这个就是个API密码。MD5 32位。
        $data=array_filter($data);
        ksort($data);
        $str='';
        foreach($data as $k=>$v) {
            $str.=$k.'='.$v.'&';
        }
        $str.='key='.$secrect_key;
        $data['sign']=md5($str);
        $xml=$this->arraytoxml($data);

        $url='https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers'; //调用接口
        $res=$this->curl($xml,$url);
        $return=$this->xmltoarray($res);


       echo "<pre>";print_r($return); die;
        //返回来的结果
        // [return_code] => SUCCESS [return_msg] => Array ( ) [mch_appid] => wxd44b890e61f72c63 [mchid] => 1493475512 [nonce_str] => 616615516 [result_code] => SUCCESS [partner_trade_no] => 20186505080216815
        // [payment_no] => 1000018361251805057502564679 [payment_time] => 2018-05-15 15:29:50


        $responseObj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
        echo $res= $responseObj->return_code;  //SUCCESS  如果返回来SUCCESS,则发生成功，处理自己的逻辑

        return $res;
    }

}
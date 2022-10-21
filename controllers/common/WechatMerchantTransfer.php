<?php
namespace app\controllers\common;

class WechatMerchantTransfer
{
    /**
     * @notes 商家转账到零钱
     * @param $batch_no //提现订单号
     * @param $left_money//提现金额 单位 元
     * @param $user_openid //用户openID
     * @param $withdraw_name //提现金额大于200,用户真实名字必填
     * @return bool
     * @throws \Exception
     * @author ljj
     * @date 2022/9/27 4:40 下午
     */
    public static function transfer()
    {
        $config = [
            'app_id' => 'wx76c8ca6b14d702a6',
            'mch_id' => '1509288651', //商户ID
            'cert_client' => 'D:\phpstudy_pro\WWW\huishou.ebaozu.com\runtime/pem/15fd8f2b92f4a0a4d045d7498273ab08', //cert证书地址//绝对路径
            'cert_key' => 'D:\phpstudy_pro\WWW\huishou.ebaozu.com\runtime/pem/64ebd66ac53c9defb1b7946f0b6bda46', //key支付证书绝对地址
        ];
        $withdrawApply = [
            'orderid' => '',

        ];
        //请求URL
        $url = 'https://api.mch.weixin.qq.com/v3/transfer/batches';
        //请求方式
        $http_method = 'POST';
        //请求参数
        $data = [
            'appid' => $config['app_id'], //申请商户号的appid或商户号绑定的appid（企业号corpid即为此appid）
            'out_batch_no' =>date('YmdHis').rand(1000, 9999),// $batch_no, //商户系统内部的商家批次单号，要求此参数只能由数字、大小写字母组成，在商户系统内部唯一
            'batch_name' => '提现至微信零钱', //该笔批量转账的名称
            'batch_remark' => '提现至微信零钱', //转账说明，UTF8编码，最多允许32个字符
            'total_amount' => 1, //转账金额单位为“分”。转账总金额必须与批次内所有明细转账金额之和保持一致，否则无法发起转账操作
            'total_num' => 1, //一个转账批次单最多发起三千笔转账。转账总笔数必须与批次内所有明细之和保持一致，否则无法发起转账操作
            'transfer_detail_list' => [
                [ //发起批量转账的明细列表，最多三千笔
                    'out_detail_no' => date('YmdHis').rand(1000, 9999), //商户系统内部区分转账批次单下不同转账明细单的唯一标识，要求此参数只能由数字、大小写字母组成
                    'transfer_amount' => 1, //转账金额单位为分
                    'transfer_remark' => '提现至微信零钱', //单条转账备注（微信用户会收到该备注），UTF8编码，最多允许32个字符
                    'openid' => 'oJk4Q5SCwC95V9vkVC8zNvNIctqk', //openid是微信用户在公众号appid下的唯一用户标识（appid不同，则获取到的openid就不同），可用于永久标记一个用户
                ]
            ]
        ];
//        if ($left_money >= 2000) {
//            if (empty($withdraw_name)) {
//                throw new \Exception('转账金额 >= 2000元，收款用户真实姓名必填');
//            }
//            $data['transfer_detail_list'][0]['user_name'] = self::getEncrypt($withdrawApply['real_name'], $config);
//        }

        $token = self::token($url, $http_method, $data, $config); //获取token
        $result = self::https_request($url, json_encode($data), $token); //发送请求
        $result_arr = json_decode($result, true);  echo "<pre>";var_dump($result_arr);die;

        if (!isset($result_arr['create_time'])) { //批次受理失败
            throw new \Exception($result_arr['message']);
        }
        //   成功返回信息  {"batch_id":"1030001036201351072852022101201442513049","create_time":"2022-10-12T22:08:21+08:00","out_batch_no":"20221011004103000000146822"}
        //批次受理成功，更新提现申请单为提现中状态
        //业务修改为提现中

        return $result_arr;
    }

    /**
     * @notes 签名生成
     * @param $url
     * @param $http_method
     * @param $data
     * @param $config
     * @return string
     * @author ljj
     * @date 2022/9/27 4:14 下午
     */
    public static function token($url, $http_method, $data, $config)
    {
        $timestamp = time(); //请求时间戳
        $url_parts = parse_url($url); //获取请求的绝对URL
        $nonce = $timestamp . rand('10000', '99999'); //请求随机串
        $body = empty($data) ? '' : json_encode((object)$data); //请求报文主体
        $stream_opts = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]
        ];

        $apiclient_cert_arr = openssl_x509_parse(file_get_contents($config['cert_client'], false, stream_context_create($stream_opts)));
        $serial_no = $apiclient_cert_arr['serialNumberHex']; //证书序列号
        $mch_private_key = file_get_contents($config['cert_key'], false, stream_context_create($stream_opts)); //密钥
        $merchant_id = $config['mch_id']; //商户id
        $canonical_url = ($url_parts['path'] . (!empty($url_parts['query']) ? "?${url_parts['query']}" : ""));
        $message = $http_method . "\n" .
            $canonical_url . "\n" .
            $timestamp . "\n" .
            $nonce . "\n" .
            $body . "\n";
        openssl_sign($message, $raw_sign, $mch_private_key, 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign); //签名
        $schema = 'WECHATPAY2-SHA256-RSA2048';
        $token = sprintf(
            'mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $merchant_id,
            $nonce,
            $timestamp,
            $serial_no,
            $sign
        ); //微信返回token
        return $schema . ' ' . $token;
    }

    /**
     * @notes 发送请求
     * @param $url
     * @param $data
     * @param $token
     * @return bool|string
     * @author ljj
     * @date 2022/9/27 4:15 下午
     */
    public static function https_request($url, $data, $token)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, (string)$url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //添加请求头
        $headers = [
            'Authorization:' . $token,
            'Accept: application/json',
            'Content-Type: application/json; charset=utf-8',
            'User-Agent:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36',
        ];
        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /**
     * @notes 敏感信息加解密
     * @param $str
     * @param $config
     * @return string
     * @throws \Exception
     * @author ljj
     * @date 2022/9/27 3:53 下午
     */
    public static function getEncrypt($str, $config)
    {
        //$str是待加密字符串
        $public_key = file_get_contents($config['cert_client']);
        $encrypted = '';
        if (openssl_public_encrypt($str, $encrypted, $public_key, OPENSSL_PKCS1_OAEP_PADDING)) {
            //base64编码
            $sign = base64_encode($encrypted);
        } else {
            throw new \Exception('encrypt failed');
        }
        return $sign;
    }

    /**
     * @notes 商家明细单号查询明细单API
     * @param $withdrawApply
     * @param $config
     * @return mixed
     * @author ljj
     * @date 2022/9/27 5:54 下午
     */
    public static function details($withdrawApply, $config)
    {
        //请求URL
        $url = 'https://api.mch.weixin.qq.com/v3/transfer/batches/out-batch-no/' . $withdrawApply['batch_no'] . '/details/out-detail-no/' . $withdrawApply['sn'];
        //请求方式
        $http_method = 'GET';
        //请求参数
        $data = [];
        $token = self::token($url, $http_method, $data, $config); //获取token
        $result = self::https_request($url, $data, $token); //发送请求
        $result_arr = json_decode($result, true);
        return $result_arr;
    }
}
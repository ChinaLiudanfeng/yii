<?php

namespace app\controllers\common;
/**
 * 微信 服务通知模板消息
 * Class WechatMerchantTransfer
 * @package app\controllers\common
 */
class ServiceNotifications
{
    /**微信小程序 推送相册完成或失败的信息***/
//    static function PushCompleteMsg($title = '', $begin_time = '', $end_time = '', $between = '', $remark = '', $open_id, $urls)
//    {
//
//        self::GetAccessTokens();
//        $url = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=' . Yii::$app->redis->get('access_token');
//        //这里是个大坑 注意我数组里的 key值，要保证和模板里的每个值都一样，如果不明白 和我上面的图片里的值对应下 你就知道怎么传了哈          $data =  array(
//        'thing1'=>array('value' => $title),
//                      'date4'=>array('value' => $begin_time),
//                      'date5'=>array('value' => $end_time),
//                      'time6'=>array('value' => $between),
//                      'thing3'=>array('value' => $remark)
//             );
//
//        if (empty($urls)) {
//            $datas = array(
//
//                'touser' => $open_id,
//                'template_id' => '5aoiQO17范德萨发范德萨发范德萨发',
//                'miniprogram_state' => 'trial',   //这个是版本 体验版 正式版啥的 这个你们定
//                'lang' => 'zh_CN',
//                'data' => $data
//            );
//        } else {
//            $datas = array(
//
//                'touser' => $open_id,
//                'template_id' => '5aoiQO173x-B打发大水',
//                'page' => $urls,  //这个是连接地址有的 需要进入小程序查看详情就加上这个参数 ，这个地址是小程序前端地址哦
//                'miniprogram_state' => 'trial',
//                'path' => $url,
//                'lang' => 'zh_CN',
//                'data' => $data
//            );
//        }
//        //最后请求第三方就可以了哈
//        $header = array("Content-Type:multipart/x-www-form-urlencoded");
//        $response = Utils::curlPost($url, $datas, 5, $header, 'json');
//        $res = json_decode($response, true);
//        print_r($res);die;
//    }

    static function curlPost($url, $post_data = array(), $timeout = 5, $header = "", $data_type = "") {
        $header = empty($header) ? '' : $header;
        //支持json数据数据提交
        if($data_type == 'json'){
            $post_string = json_encode($post_data);
        }elseif($data_type == 'array') {
            $post_string = $post_data;
        }elseif(is_array($post_data)){
            $post_string = http_build_query($post_data, '', '&');
        }

        $ch = curl_init();    // 启动一个CURL会话
        curl_setopt($ch, CURLOPT_URL, $url);     // 要访问的地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // 对认证证书来源的检查   // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        //curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($ch, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);     // Post提交的数据包
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);     // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // 获取的信息以文件流的形式返回
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
        $result = curl_exec($ch);

        // 打印请求的header信息
        //$a = curl_getinfo($ch);
        //var_dump($a);

        curl_close($ch);
        return $result;
    }

}
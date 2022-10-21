<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/21
 * Time: 3:46 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wechat\forms\mall;

use app\core\response\ApiCode;
use app\helpers\ArrayHelper;
use app\plugins\wechat\forms\Model;
use app\plugins\wechat\models\WechatConfig;
use app\plugins\wechat\models\WechatWxmpprograms;
use luweiss\Wechat\Wechat;
use luweiss\Wechat\WechatException;

class WechatConfigForm extends Model
{
    public $appid;
    public $appsecret;
    public $name;
    public $logo;
    public $qrcode;

    public function rules()
    {
        return [
            [['appid', 'appsecret', 'name', 'logo', 'qrcode'], 'required'],
            [['appid', 'appsecret', 'name', 'logo', 'qrcode'], 'trim'],
            [['appid', 'appsecret', 'name', 'logo', 'qrcode'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'appid' => '微信公众平台AppId',
            'appsecret' => '微信公众平台appSecret',
            'name' => '公众号名称',
            'logo' => '公众号logo',
            'qrcode' => '公众号二维码'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $wechatConfig = WechatConfig::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        if (!$wechatConfig) {
            $wechatConfig = new WechatConfig();
            $wechatConfig->mall_id = \Yii::$app->mall->id;
            $wechatConfig->is_delete = 0;
        }

        try {
            if ($this->appid || $this->appsecret) {
                $wechat = new Wechat([
                    'appId' => $this->appid,
                    'appSecret' => $this->appsecret,
                ]);
                $wechat->getAccessToken(true);
            }
        } catch (WechatException $e) {
            if ($e->getRaw()['errcode'] == '40013') {
                $message = '微信公众平台AppId有误(' . $e->getRaw()['errmsg'] . ')';
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $message,
                ];
            }
            if ($e->getRaw()['errcode'] == '40125') {
                $message = '微信公众平台appSecret有误(' . $e->getRaw()['errmsg'] . ')';
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $message,
                ];
            }
        }

        $wechatConfig->attributes = $this->attributes;

        if (!$wechatConfig->save()) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '保存失败',
            ];
        }
        $indexForm = new IndexForm();
        $data = $indexForm->getPath();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功',
            'data' => $data
        ];
    }

    public function getDetail()
    {
        $wechatConfig = WechatConfig::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        $third = WechatWxmpprograms::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        $data['has_third_permission'] = in_array('wxmpplatform', $permission);
        if (!$wechatConfig && !$third) {
            $data['msg'] = '信息暂未配置';
            return $this->fail($data);
        }

        $data['detail'] = $wechatConfig ? ArrayHelper::filter($wechatConfig->attributes, [
            'appid', 'appsecret', 'name', 'logo', 'qrcode'
        ]) : [];
        $data['third'] = $third;
        $indexForm = new IndexForm();
        $data = array_merge($data, $indexForm->getPath());
        return $this->success($data);
    }
}

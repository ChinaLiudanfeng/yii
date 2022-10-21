<?php

namespace app\forms\common\order\send\model;

use app\forms\common\order\send\job\CityServiceJob;
use app\forms\common\order\send\model\BaseModel;

class WechatModel extends BaseModel
{
    public $data;
    public $deliveryId;

    /**
     * 预下单数据
     * @return [type] [description]
     */
    public function getPreAddOrder()
    {
        switch ($this->deliveryId) {
            // 顺丰
            case 'SFTC':

                break;
            // 闪送
            case 'SS':
                $this->data['order_info']['is_direct_delivery'] = 1;
                break;
            // 达达
            case 'DADA':

                break;
            // 美团
            case 'MTPS':
                $id = isset($this->data['delivery_service_code']) ? (int)$this->data['delivery_service_code'] : 4002;
                $this->data['order_info']['delivery_service_code'] = $id;
                break;
            default:
                throw new \Exception('微信配送，未知配送公司');
                break;
        }

        return $this->data;
    }

    public function getAddOrder()
    {
        return $this->data;
    }

    public function mockUpdateOrder($debug = false, $shopOrderId, $waybillId)
    {
        if (!$debug) {
            \Yii::warning('微信配送未开启Debug模式 不进行模拟配送' . $shopOrderId . '-' . $waybillId);
            return false;
        }

        // 分配骑手
        \Yii::$app->queue->delay(10)->push(new CityServiceJob([
            'shopOrderId' => $shopOrderId,
            'waybillId' => $waybillId,
            'status' => 102,
            'instance' => $this->getInstance(),
        ]));
        // 骑手取货
        \Yii::$app->queue->delay(20)->push(new CityServiceJob([
            'shopOrderId' => $shopOrderId,
            'waybillId' => $waybillId,
            'status' => 202,
            'instance' => $this->getInstance(),
        ]));
        // 配送中
        \Yii::$app->queue->delay(30)->push(new CityServiceJob([
            'shopOrderId' => $shopOrderId,
            'waybillId' => $waybillId,
            'status' => 301,
            'instance' => $this->getInstance(),
        ]));
        // 配送完成
        \Yii::$app->queue->delay(40)->push(new CityServiceJob([
            'shopOrderId' => $shopOrderId,
            'waybillId' => $waybillId,
            'status' => 302,
            'instance' => $this->getInstance(),
        ]));
    }
}

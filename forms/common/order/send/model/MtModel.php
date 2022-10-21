<?php

namespace app\forms\common\order\send\model;

use app\forms\common\order\send\job\MtCityServiceJob;
use app\forms\common\order\send\model\BaseModel;

class MtModel extends BaseModel
{
    public $data;
    public $cityPreviewOrder;

    /**
     * 预下单数据
     * @return [type] [description]
     */
    public function getPreAddOrder()
    {
        $data = $this->data;

        if (isset($data['outer_order_source_desc'])) {
            if (is_numeric($data['outer_order_source_desc'])) {
                $data['outer_order_source_desc'] = $data['outer_order_source_desc'] ? (int)$data['outer_order_source_desc'] : '其它';
            } else {
                $data['outer_order_source_desc'] = $data['outer_order_source_desc'] ?: '其它';
            }
        }

        $data['delivery_service_code'] = isset($data['delivery_service_code']) ? (int)$data['delivery_service_code'] : 4002;
        $data['delivery_id'] = time();

        $data['receiver']['lat'] = substr($data['receiver']['lat'], 0, 9) * pow(10, 6);
        $data['receiver']['lng'] = substr($data['receiver']['lng'], 0, 9) * pow(10, 6);
        $data['sender']['lat'] = substr($data['sender']['lat'], 0, 9) * pow(10, 6);
        $data['sender']['lng'] = substr($data['sender']['lng'], 0, 9) * pow(10, 6);

        $goodsDetail = [];
        foreach ($data['cargo']['goods_detail']['goods'] as $key => $item) {
            $goodsDetail['goods'][] = [
                'goodName' => $item['good_name'],
                'goodPrice' => $item['good_price'],
                'goodCount' => (int) $item['good_count'],
                'goodUnit' => $item['good_unit'],
            ];
        }

        return [
            'delivery_id' => $data['delivery_id'],
            'order_id' => $data['shop_order_id'],
            'outer_order_source_desc' => $data['outer_order_source_desc'],
            'delivery_service_code' => $data['delivery_service_code'],
            'receiver_name' => $data['receiver']['name'],
            'receiver_address' => $data['receiver']['address'] . $data['receiver']['address_detail'],
            'receiver_phone' => $data['receiver']['phone'],
            'receiver_lng' => $data['receiver']['lng'],
            'receiver_lat' => $data['receiver']['lat'],
            'goods_value' => $data['cargo']['goods_value'],
            'goods_weight' => $data['cargo']['goods_weight'] > 0 ? doubleval($data['cargo']['goods_weight']) : 1,
            'goods_detail' => json_encode($goodsDetail, JSON_UNESCAPED_UNICODE),
            'poi_seq' => rand(1,9999),
        ];
    }

    public function getAddOrder()
    {
        $array = $this->cityPreviewOrder->order_info;
        $array['shop_order_id'] = $this->cityPreviewOrder->all_order_info['shop_order_id'];
        return $array;
    }

    /**
    * $data['preview_order_id']
    * $data['instance']
    */
    public function mockUpdateOrder($data)
    {
        switch ($data['type']) {
            // 模拟1 接单-取货-送达
            case 1:
                // 分配骑手
                \Yii::$app->queue->delay(10)->push(new MtCityServiceJob([
                    'mock_type' => 'arrange',
                    'data' => $data
                ]));
                // 骑手取货
                \Yii::$app->queue->delay(20)->push(new MtCityServiceJob([
                    'mock_type' => 'pickup',
                    'data' => $data
                ]));
                // 配送完成
                \Yii::$app->queue->delay(30)->push(new MtCityServiceJob([
                    'mock_type' => 'deliver',
                    'data' => $data
                ]));
                break;

            // 模拟2 接单-取消订单
            case 2:
                // 分配骑手
                \Yii::$app->queue->delay(10)->push(new MtCityServiceJob([
                    'mock_type' => 'arrange',
                    'data' => $data
                ]));

                \Yii::$app->queue->delay(20)->push(new MtCityServiceJob([
                    'mock_type' => 'cancelOrder',
                    'data' => $data
                ]));
                break;

            // 模拟3 接单-取货-取消订单
            case 3:
                // 分配骑手
                \Yii::$app->queue->delay(10)->push(new MtCityServiceJob([
                    'mock_type' => 'arrange',
                    'data' => $data
                ]));

                // 骑手取货
                \Yii::$app->queue->delay(20)->push(new MtCityServiceJob([
                    'mock_type' => 'pickup',
                    'data' => $data
                ]));

                \Yii::$app->queue->delay(30)->push(new MtCityServiceJob([
                    'mock_type' => 'cancelOrder',
                    'data' => $data
                ]));
                break;
            
            default:
                throw new \Exception('未知模拟流程');
                break;
        }
    }
}

<?php
/**
 * @Descripttion:
 * @Author: liudanfeng <1594275407@qq.com>
 * @Date:2022/10/19 15:13
 */

namespace app\controllers\common;

use app\models\CommissionIssued;
use app\models\IssuedErrorLog;

/**
 *  暂时 测试写定时任务
 * Class GrantController
 * @package app\controllers\common
 */
class GrantController
{
    //todo  用户余额页面 增加个提示语 平台扣取 5% 。20% 自动分到个人账户零钱


    public function execute()
    {
        /**
         * 执行 佣金发送表
         * 没天 8点发送佣金
         * todo  佣金表 发放佣金 调用零钱，成功后，存入 发放日志表，存入平台扣取流水表， 平台根据流水表 渲染后台页面
         */

        $nowTime = time();
        $nowEight = strtotime('8:00');
//        if($nowTime==$nowEight){
        $commissionModel = CommissionIssued::find()->where(['is_all_issue' => 1])->asArray()->all();
        // TODO  获取 获取 数据 发放零钱
        // todo 20%零钱发放
//           $this-> epd($commissionModel);
        $failErrorLog = $successData = $naamData = [];// 零钱发放失败 日志数组
        foreach ($commissionModel as $key => $value) {
            $naamPrice = bcmul($value['price'], '0.02', 2);
            // 发放零钱 - 数字更换地方 -
//                $send = (new WechatMerchantTransfer())->transfer(bcmul($value['price'],$naamPrice,2));; //todo 暂时假定发放零钱 好使。  发放零钱时调用下 即可，
            $send['code'] = true;
            if (!$send['code']) {//失败日志
                $failErrorLog = array_merge($failErrorLog, ['commission_id' => $value['id'], 'how_many_days' => $value['issued_day'] + 1, 'message' => $send['msg'], 'create_time' => time()]);
            } else { // 成功  更改表数据
                // 更改发放表

                $successData[] = [
                    'id' => $value['id'],
                    'issued' => bcadd($value['issued'], $value['price'], 2), //已发放金额
                    'unissued' => bcsub($value['unissued'], $value['price'], 2), // 未发放金额   --  扣流水
                    'issued_day' => $value['issued_day'] + 1, // 发放天数
                    'is_all_issue' => ($value['issued_day'] + 1) == 30 ? 2 : 1, // 是否全部都发放 2 全发 1 未全发
                    'update_at' => time() // 更新时间
                ];
                // 存入平台流水日志表
                $naamData[] = [
                    'mall_id' => $value['mall_id'],
                    'order_id' => $value['order_id'],
                    'commission_id' => $value['id'],
                    'naam_money' => $naamPrice,
                    'create_time' => time()
                ];
            }
        }

        // 批量加入 错误日志
        if (!empty($failErrorLog)) saveAll('zjhj_bd_issued_error_log', ['commission_id', 'how_many_days', 'message', 'create_time'], $failErrorLog);
        // todo 批量更改 佣金发放表
//        epd($successData);
        if(!empty($successData))  updateAll('zjhj_bd_commission_issued',$successData);
        // 批量更改 平台扣取流水表
        if (!empty($naamData)) saveAll('zjhj_bd_naam', ['mall_id', 'order_id', 'commission_id', 'naam_money', 'create_time'], $naamData);





        }


    }



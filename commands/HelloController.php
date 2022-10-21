<?php
namespace app\commands;
use app\controllers\common\WechatChangeController;
use app\controllers\common\WechatMerchantTransfer;
use yii\console\Controller;
use app\models\CommissionIssued;
class HelloController extends  Controller
{
    /**
     * 5465465465465436546
     * @param string $message the message to be echoed.
     */
    public function actionIndex($message = 'hello world')
    {
       /**
        * 执行 佣金发送表
        * 没天 8点发送佣金
        * todo  佣金表 发放佣金 调用零钱，成功后，存入 发放日志表，存入平台扣取流水表， 平台根据流水表 渲染后台页面
        */
//       $model = CommissionIssued ::find()->where(['is_all_issue'=>1])->asArray()->all();
        // TODO 写定时任务


      // 打到零钱账户上
        (new WechatMerchantTransfer())->transfer();die; //todo 暂时假定发放零钱 好使。  发放零钱时调用下 即可，
        //todo 存入日志表，  先写 流水扣取表

    }
}
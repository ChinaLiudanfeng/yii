<?php

namespace app\models;

use Yii;

/**
 * 佣金发放表
 * @property int user_id
 * @property int mall_id
 * @property int order_id
 * @property int create_at
 * @property string brokerage
 * @property string unissued
 * @property string issued_day
 * @property string price
 * @property string is_all_issue
 * @property string garde
 * @property int id
 */
class CommissionIssued extends ModelActiveRecord
{

    public static function tableName()
    {
        return '{{%commission_issued}}';
    }
}
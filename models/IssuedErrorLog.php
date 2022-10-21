<?php

namespace app\models;

use Yii;

/**
 * 零钱发放日志表
 */
class IssuedErrorLog extends ModelActiveRecord
{

    public static function tableName()
    {
        return '{{%naam}}';
    }
}
<?php

namespace app\models;

use Yii;

/**
 *  平台流水扣取表
 */
class Naam extends ModelActiveRecord
{

    public static function tableName()
    {
        return '{{%naam}}';
    }
}
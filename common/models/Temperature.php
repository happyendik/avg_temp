<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * Class Temperature
 * @package common\models
 *
 * @property integer $id
 * @property string $time
 * @property string $temperature
 */
class Temperature extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%temperature}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['time', 'temperature'], 'required'],
            ['time', 'datetime', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'time' => 'Час',
            'temperature' => 'temperature'
        ];
    }
}
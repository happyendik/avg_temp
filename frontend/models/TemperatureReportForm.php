<?php

namespace frontend\models;

use yii\base\Model;

class TemperatureReportForm extends Model
{
    public $fromMonth;

    public $toMonth;

    public $fromYear;

    public $toYear;

    public $months = [
        '01' => 'Январь',
        '02' => 'Февраль',
        '03' => 'Март',
        '04' => 'Апрель',
        '05' => 'Май',
        '06' => 'Июнь',
        '07' => 'Июль',
        '08' => 'Август',
        '09' => 'Сентябрь',
        '10' => 'Октябрь',
        '11' => 'Ноябрь',
        '12' => 'Декабрь'
    ];

    public $years = [
        '2016' => '2016',
        '2017' => '2017'
    ];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fromMonth', 'toMonth', 'fromYear', 'toYear'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'fromMonth' => 'Начальный месяц',
            'fromYear' => 'Начальный год',
            'toMonth' => 'Конечный месяц',
            'toYear' => 'Конечный год'
        ];
    }
}
<?php

namespace console\controllers;

use yii\console\Controller;
use common\models\Temperature;

/**
 * Class WeatherApiController
 * @package console\controllers
 */
class WeatherApiController extends Controller
{
    /**
     * @throws \Exception
     */
    public function actionGenerateData()
    {
        $startTime = new \DateTime('2016-01-01 00:00:00', new \DateTimeZone('UTC'));
        $endTime = new \DateTime('2018-01-01 00:00:00', new \DateTimeZone('UTC'));
        $interval = new \DateInterval('PT1H');

        $errors = [];

        while ($startTime < $endTime) {
            $model = new Temperature();
            $model->time = $startTime->format('Y-m-d H:i:s');
            $model->temperature = random_int(-30, 30);

            if ($model->save()) {
                echo '+';
            } else {
                $errors[] = [
                    $startTime->format('Y-m-d H:i:s') => $model->getErrors()
                ];
                echo '_';
            }
            $startTime->add($interval);
        }

        if ($errors) {
            print_r($errors);
        }
    }
}
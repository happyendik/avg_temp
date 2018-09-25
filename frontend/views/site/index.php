<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var \frontend\models\TemperatureReportForm $model
 * @var array $models
 * @var DateTime $startTime
 * @var DateTime $endTime
 */

$this->title = 'Отчет по среднесуточной температуре';

?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Congratulations!</h1>
    </div>

    <?php
    $form = ActiveForm::begin();
    ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'fromMonth')->dropDownList($model->months) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'fromYear')->dropDownList($model->years) ?>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'toMonth')->dropDownList($model->months) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'toYear')->dropDownList($model->years) ?>
        </div>
    </div>
    <hr>
    <div class="form-group">
        <?= Html::submitButton('Сформировать отчет', ['class' => 'btn btn-primary' ]) ?>
    </div>

    <?php
    $form->end();
    ?>


    <div class="body-content">
        <?php if ($models) : ?>

            <?php foreach ($models as $month) : ?>
                <table class="table table-bordered">
                    <th><?= $month->name ?></th>
                    <tr>
                        <td>№</td><td>Пн</td><td>Вт</td><td>Ср</td><td>Чт</td><td>Пт</td><td>Сб</td><td>Вск</td>
                    </tr>
                    <?php foreach ($month->weeks as $key => $week) : ?>
                        <tr>
                            <td><?= $key ?></td>

                            <?php for ($i = 1; $i <= 7; $i++) : ?>
                                <?php if (array_key_exists($i, $week['days'])) : ?>

                                    <?php
                                    if ($month->maxAmplitude === $week['days'][$i]['amplitude']) {
                                        echo '<td class="danger">';
                                    } elseif ($week['maxAmplitude'] === $week['days'][$i]['amplitude']) {
                                        echo '<td class="warning">';
                                    } else {
                                        echo '<td>';
                                    }
                                    ?>

                                    <?= Yii::$app->formatter->asDate($week['days'][$i]['date'], 'php:d M')
                                        . ' (' . $week['days'][$i]['day_temp'] . '/' . $week['days'][$i]['night_temp'] . ')' ?>
                                    </td>
                                <?php else : ?>
                                    <td></td>
                                <?php endif; ?>

                            <?php endfor; ?>

                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>
</div>

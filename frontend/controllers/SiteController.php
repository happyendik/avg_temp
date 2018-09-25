<?php
namespace frontend\controllers;

use common\models\Temperature;
use frontend\models\TemperatureReportForm;
use frontend\models\Week;
use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use frontend\models\Month;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionIndex()
    {
        $model = new TemperatureReportForm();
        $models = [];
        $startTime = null;
        $endTime = null;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // validation in model
            $startTimeStr = $model->fromYear . '-' . $model->fromMonth . '-01 00:00:00';
            $endTimeStr = $model->toYear . '-' . $model->toMonth . '-01 00:00:00';

            $startTime = new \DateTime($startTimeStr, new \DateTimeZone('UTC'));
            $endTime = new \DateTime($endTimeStr, new \DateTimeZone('UTC'));

            $endTime = $endTime->add(new \DateInterval('P1M'));

            $connection = Yii::$app->db;
            $models = $connection->createCommand("
                SELECT 
                DATE(`time`) AS `date`,
                AVG(CASE 
                  WHEN HOUR(`time`) BETWEEN 0 AND 7 THEN `temperature` 
                  WHEN HOUR(`time`) BETWEEN 20 AND 23 THEN `temperature` END) AS `night_temp`,
                AVG(CASE 
                  WHEN HOUR(`time`) BETWEEN 8 AND 19 THEN `temperature` END) AS `day_temp`
                FROM temperature
                WHERE (`time` > '{$startTime->format('Y-m-d H:i:s')}')
                AND (`time` < '{$endTime->format('Y-m-d H:i:s')}')
                GROUP BY DATE(`time`);
                ")->queryAll();

            $models = $this->prepareData($models, $startTime, $endTime);
        }

        return $this->render('index', [
            'model' => $model,
            'models' => $models,
            'startTime' => $startTime,
            'endTime' => $endTime
        ]);
    }

    public function prepareData(array $models, \DateTime $start, \DateTime $end)
    {
        $months = [];

        while ($start < $end) {
            $month = new Month();
            $month->name = $start->format('M Y');

            $maxAmplitude = 0;
            foreach ($models as $model) {
                if (strpos($model['date'], $start->format('Y-m')) !== 0) {
                    continue;
                }

                $weekNumber = date('W', strtotime($model['date']));
                $dayOfWeek = date('w', strtotime($model['date']));

                if ($dayOfWeek === '0') {
                    $dayOfWeek = '7';
                }

                $month->weeks[$weekNumber]['days'][$dayOfWeek] = [
                    'night_temp' => round($model['night_temp']),
                    'day_temp' => round($model['day_temp']),
                    'date' => $model['date'],
                    'amplitude' => abs(round($model['night_temp']) - round($model['day_temp']))
                ];

                if (
                    !array_key_exists('maxAmplitude', $month->weeks[$weekNumber]) ||
                    $month->weeks[$weekNumber]['maxAmplitude'] < abs(round($model['night_temp']) - round($model['day_temp']))
                ) {
                    $month->weeks[$weekNumber]['maxAmplitude'] = abs(round($model['night_temp']) - round($model['day_temp']));
                }

                if ($maxAmplitude < abs(round($model['night_temp']) - round($model['day_temp']))) {
                    $maxAmplitude = abs(round($model['night_temp']) - round($model['day_temp']));
                }
            }

            $month->maxAmplitude = $maxAmplitude;
            $months[] = $month;
            $start->add(new \DateInterval('P1M'));
        }

        return $months;
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
}

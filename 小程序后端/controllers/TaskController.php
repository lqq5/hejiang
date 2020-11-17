<?php
/**
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/24 9:22
 */


namespace app\controllers;

/**
 * 定时任务接收控制器
 * Class TaskController
 * @package app\controllers
 */
class TaskController extends Controller
{
    public function init()
    {
        parent::init();
        $this->enableCsrfValidation = false;
    }

    public function actionIndex($token)
    {
        \Yii::$app->task->run($token);
    }
}
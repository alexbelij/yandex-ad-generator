<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 28.04.16
 * Time: 21:31
 */

namespace app\widgets\taskButton;

use app\models\TaskQueue;
use app\models\YandexUpdateLog;
use yii\base\Widget;

class TaskButtonWidget extends Widget
{
    /**
     * @var TaskQueue
     */
    public $task;

    /**
     * @var string
     */
    public $caption;

    /**
     * @var string
     */
    public $buttonClass;

    /**
     * @var array
     */
    public $attributes = [];

    /**
     * @inheritDoc
     */
    public function run()
    {
        if ($this->task) {
            $hasError = YandexUpdateLog::hasError($this->task) || $this->task->status == TaskQueue::STATUS_ERROR;
        } else {
            $hasError = false;
        }
        
        return $this->render('index', [
            'task' => $this->task,
            'caption' => $this->caption,
            'buttonClass' => $this->buttonClass,
            'hasError' => $hasError,
            'attributes' => $this->attributes
        ]);
    }
}

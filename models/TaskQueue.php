<?php

namespace app\models;

use app\lib\api\yandex\direct\exceptions\YandexException;
use app\lib\PointsCalculator;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "task_queue".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $started_at
 * @property string $status
 * @property string $operation
 * @property string $completed_at
 * @property string $context
 * @property string $error
 * @property int $shop_id
 * @property string $hash
 * @property int $total_points
 * @property string $log_file
 * @property string $info
 *
 * @property Shop $shop
 * @property YandexUpdateLog[] $logs
 */
class TaskQueue extends \yii\db\ActiveRecord
{
    const STATUS_READY = 'ready';
    const STATUS_RUN = 'run';
    const STATUS_ERROR = 'error';
    const STATUS_SUCCESS = 'success';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'task_queue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'completed_at', 'started_at'], 'safe'],
            [['context', 'error', 'hash', 'log_file'], 'string'],
            [['status', 'operation'], 'string', 'max' => 50],
            ['status', 'default', 'value' => self::STATUS_READY],
            [['shop_id', 'total_points'], 'integer'],
            [['info'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата создания',
            'started_at' => 'Дата запуска',
            'status' => 'Статус',
            'operation' => 'Тип операции',
            'completed_at' => 'Дата завершения',
            'context' => 'Контекст',
            'error' => 'Ошибка',
            'log_file' => 'Файл лога',
            'info' => 'Доп. информация'
        ];
    }

    /**
     * Имеются ли таски на выполнение
     *
     * @param int $shopId
     * @param string $operation
     * @param array $context
     * @return bool
     */
    public static function hasActiveTasks($shopId, $operation, $context = [])
    {
        $query = self::find()
            ->andWhere([
                'shop_id' => $shopId,
                'operation' => $operation,
                'status' => [self::STATUS_READY, self::STATUS_RUN]
            ]);

        if ($context) {
            $query->andWhere(['hash' => md5(json_encode($context))]);
        }

        return $query->exists();
    }

    /**
     * Возвращает задачу на выполнение
     *
     * @return $this
     * @throws \yii\db\Exception
     */
    public static function getNextTaskForRun()
    {
        $transaction = Yii::$app->db->beginTransaction();
        $sql = 'SELECT * FROM task_queue WHERE status = :status ORDER BY id ASC FOR UPDATE';
        $task = self::findBySql($sql, [':status' => self::STATUS_READY])->one();
        if ($task) {
            $task->status = self::STATUS_RUN;
            $task->save();
        }
        $transaction->commit();

        return $task;
    }

    /**
     * Создание нового таска
     *
     * @param int $shopId
     * @param string $operation
     * @param array $context
     * @param string $info
     * @param array $hashFields
     * @return TaskQueue
     */
    public static function createNewTask(
        $shopId, $operation, array $context = [], $info = '', $hashFields = []
    ) {
        $task = new TaskQueue([
            'shop_id' => $shopId,
            'status' => self::STATUS_READY,
            'operation' => $operation,
            'context' => !empty($context) ? json_encode($context) : null,
            'info' => $info
        ]);

        if ($context) {
            $hashData = $context;
            if ($hashFields) {
                $hashData = array_intersect_key($context, array_flip($hashFields));
            }
            $task->hash = md5(json_encode($hashData));
        }

        $task->save();

        return $task;
    }

    /**
     * Помечаем задачу как запущенную
     */
    public function markRun()
    {
        $this->started_at = date('Y-m-d H:i:s');
        $this->status = self::STATUS_RUN;
        $this->save();
    }

    /**
     * Помечаем задачу как завершенную
     */
    public function markCompleted()
    {
        $this->completed_at = date('Y-m-d H:i:s');
        $this->status = self::STATUS_SUCCESS;
        $this->save();
    }

    /**
     * Задача завершилась с ошибкой
     *
     * @param \Exception $e
     */
    public function markError(\Exception $e)
    {
        $this->completed_at = date('Y-m-d H:i:s');
        $this->status = self::STATUS_ERROR;
        if ($e instanceof YandexException) {
            $this->error = $e->getCode() . ': ' . $e->getMessage();
        } else {
            $this->error = 'File: ' . $e->getFile() . ', line: ' . $e->getLine() . ', code: ' . $e->getCode() .
                ', message: ' . $e->getMessage() . ', trace: ' . $e->getTraceAsString();
        }

        $this->save();
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return json_decode($this->context, true);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'shop_id']);
    }

    /**
     * @param int $shopId
     * @param string $operation
     * @return $this
     */
    public static function getLastRunnedFor($shopId, $operation)
    {
        return self::find()->andWhere([
            'shop_id' => $shopId,
            'status' => [self::STATUS_SUCCESS, self::STATUS_ERROR],
            'operation' => $operation
        ])->orderBy('id DESC')->one();
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($insert)
    {
        if ($this->context && !$this->hash) {
            $this->hash = md5($this->context);
        }
        $this->total_points = $this->getPointsCalculator()->getTotal();
        $this->getPointsCalculator()->reset();

        return parent::beforeSave($insert);
    }

    protected function getPointsCalculator()
    {
        return PointsCalculator::getInstance();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogs()
    {
        return $this->hasMany(YandexUpdateLog::className(), ['task_id' => 'id']);
    }

    /**
     * Возвращает кол-во задач с ошибкой после переданного времени
     *
     * @param int $shopId
     * @param $operation
     * @param $timestamp
     * @return int|string
     */
    public static function getErrorsCount($shopId, $operation, $timestamp)
    {
        return self::find()
            ->andWhere([
                'operation' => $operation,
                'status' => self::STATUS_ERROR,
                'shop_id' => $shopId
            ])
            ->andWhere(['>=', 'created_at', date('Y-m-d H:i', $timestamp)])
            ->count();

    }

    /**
     * @param Shop $shop
     * @param string $operation
     * @param int|null $time
     * @return bool
     */
    public static function hasActiveTask(Shop $shop, $operation, $time = null)
    {
        $time = $time ?: time();

        return TaskQueue::find()
            ->andWhere([
                'operation' => $operation,
                'status' => [TaskQueue::STATUS_READY, TaskQueue::STATUS_RUN, TaskQueue::STATUS_SUCCESS],
                'shop_id' => $shop->id
            ])
            ->andWhere(['>=', 'created_at', date('Y-m-d H:i', $time)])
            ->exists();
    }

    /**
     * @return number
     */
    public function getTotalPoints()
    {
        return is_null($this->total_points) ?
            array_sum(ArrayHelper::getColumn($this->logs, 'points')) :
            $this->total_points;
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return in_array($this->status, [self::STATUS_ERROR, self::STATUS_SUCCESS]);
    }

    /**
     * @return array
     */
    public static function getStatusesList()
    {
        return [
            self::STATUS_ERROR => 'Ошибка',
            self::STATUS_SUCCESS => 'Успех',
            self::STATUS_READY => 'Ожидает',
            self::STATUS_RUN => 'Выполняется'
        ];
    }

    /**
     * @return string
     */
    public function getStatusLabel()
    {
        if ($this->isFinished()) {
            $hasError = YandexUpdateLog::find()
                ->andWhere([
                    'task_id' => $this->primaryKey,
                    'status' => 'error'
                ])->exists();

            $status = ($hasError || $this->status == 'error') ?
                self::STATUS_ERROR : self::STATUS_SUCCESS;
        } else {
            $status = $this->status;
        }

        return self::getStatusesList()[$status];
    }
}

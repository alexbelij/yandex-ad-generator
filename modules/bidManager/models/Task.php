<?php

namespace app\modules\bidManager\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "bid_task".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $started_at
 * @property string $finished_at
 * @property integer $account_id
 * @property string $status
 * @property string $task
 * @property string $message
 * @property string $context
 * @property int $total_points
 *
 * @property Account $account
 * @property TaskLog[] $bidTaskLogs
 */
class Task extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ERROR = 'error';
    const STATUS_PROCESSING = 'processing';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bid_task';
    }

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => new Expression('now()'),
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_at']
                ]
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'started_at', 'finished_at'], 'safe'],
            [['account_id', 'total_points'], 'integer'],
            [['context'], 'safe'],
            [['message'], 'string'],
            [['status', 'task'], 'string', 'max' => 255],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['account_id'], 'exist', 'skipOnError' => true, 'targetClass' => Account::className(), 'targetAttribute' => ['account_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Время создания',
            'started_at' => 'Время запуска',
            'finished_at' => 'Время завершения',
            'account_id' => 'Аккаунт',
            'status' => 'Статус',
            'task' => 'Задача',
            'message' => 'Сообщение',
            'context' => 'Контекст',
            'total_points' => 'Потраченные баллы'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'account_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBidTaskLogs()
    {
        return $this->hasMany(TaskLog::className(), ['task_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getTaskLabel()
    {
        $task = new $this->task($this);

        return $task->getName();
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
        $sql = 'SELECT * FROM bid_task WHERE status = :status ORDER BY id ASC LIMIT 1 FOR UPDATE';
        /** @var Task $task */
        $task = self::findBySql($sql, [':status' => self::STATUS_PENDING])->one();
        if ($task) {
            $task->status = self::STATUS_PROCESSING;
            $task->save();
        }
        $transaction->commit();

        return $task;
    }

    /**
     * Имеются ли таски на выполнение
     *
     * @param int $accountId
     * @param string $task
     * @param array $context
     * @return bool
     */
    public static function hasActiveTasks($accountId, $task, $context = [])
    {
        $query = self::find()
            ->andWhere([
                'account_id' => $accountId,
                'task' => $task,
                'status' => [self::STATUS_PENDING, self::STATUS_PROCESSING]
            ]);

        return $query->exists();
    }

    /**
     * Создание нового таска
     *
     * @param int $accountId
     * @param string $task
     * @param array $context
     * @return Task
     */
    public static function createNewTask(
        $accountId, $task, array $context = []
    ) {
        $taskInstance = new Task([
            'account_id' => $accountId,
            'status' => self::STATUS_PENDING,
            'task' => $task,
            'context' => !empty($context) ? json_encode($context) : null,
        ]);

        $taskInstance->save();

        return $taskInstance;
    }
}

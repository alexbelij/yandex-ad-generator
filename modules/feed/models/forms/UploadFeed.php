<?php

namespace app\modules\feed\models\forms;

use app\lib\UploadedFile;
use app\modules\feed\lib\FeedException;
use app\modules\feed\lib\FeedReplacer;
use app\modules\feed\models\Feed;
use app\modules\feed\models\FeedQueue;
use yii\base\Model;

/**
 * Class UploadFeed
 * @package app\modules\feed\models\forms
 */
class UploadFeed extends Model
{
    /**
     * @var string
     */
    public $feedFile;

    /**
     * @var Feed
     */
    public $feed;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        if (!($this->feed instanceof Feed)) {
            throw new FeedException('Необходимо передать модель фида');
        }
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['feedFile'], 'required'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'feedFile' => 'Файл фида (формат yandex yml)'
        ];
    }

    /**
     * @return bool
     * @throws FeedException
     */
    public function process()
    {
        $feedFile = UploadedFile::getInstance($this, 'feedFile');

        if (!$feedFile) {
            $this->addError('feedFile', 'Ошибка при загрузке фида');
            return null;
        }

        $directory = \Yii::getAlias('@app/feeds/unprocessed');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $sourceFile = $directory  . '/' . md5($feedFile->tempName) . '.' . $feedFile->getExtension();

        if (!$feedFile->saveAs($sourceFile)) {
            $this->addError('feedFile', 'Произошла ошибка при сохранении файла');
        }

        $feedQueue = new FeedQueue([
            'original_filename' => $feedFile->name,
            'source_file' => $sourceFile,
            'feed_id' => $this->feed->primaryKey,
            'size' => $feedFile->size,
        ]);

        return $feedQueue->save();
    }
}

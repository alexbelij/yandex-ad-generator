<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 13.11.16
 * Time: 6:57
 */

namespace app\lib\import\yml\extensions;
use app\models\Shop;
use app\models\WordException;

/**
 * Class ColorSaver
 * @package app\lib\import\yml\extensions
 */
class WordExceptionSaver
{
    /**
     * @var array
     */
    protected $phrases = [];

    /**
     * @var Shop
     */
    protected $shop;

    /**
     * @var bool
     */
    protected $hasNew = false;

    /**
     * @var array
     */
    protected $sortedPhrases = [];

    /**
     * Недавно добавленные фразы
     *
     * @var array
     */
    protected $latestAdded = [];

    /**
     * WordExceptionSaver constructor.
     * @param Shop $shop
     */
    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    /**
     * Добавление фразы исключения
     *
     * @param string $phrase
     * @return $this
     */
    public function addPhrase($phrase)
    {
        if (!in_array($phrase, $this->phrases)) {
            $this->phrases[] = $phrase;
            $this->hasNew = true;
        }

        return $this;
    }

    /**
     * @param array $phrases
     * @return $this
     */
    public function addPhrases(array $phrases)
    {
        foreach ($phrases as $phrase) {
            $this->addPhrase($phrase);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getSortedPhrases()
    {
        if ($this->hasNew || empty($this->sortedPhrases)) {
            $this->hasNew = false;
            $this->sortedPhrases = $this->phrases;
            usort($this->sortedPhrases, function ($a, $b) {
                return substr_count($a, ' ') < substr_count($b, ' ');
            });
        }

        return $this->sortedPhrases;
    }

    /**
     * Бы ли изменен список
     *
     * @return bool
     */
    public function isModified()
    {
        return $this->hasNew;
    }

    /**
     * @return array
     */
    public function getPhrases()
    {
        return $this->phrases;
    }

    /**
     * @return bool
     */
    public function save()
    {
        $phrases = WordException::find()
            ->andWhere(['shop_id' => $this->shop->id])
            ->asArray()
            ->indexBy('word')
            ->all();

        $toSave = [];
        foreach ($this->phrases as $phrase) {
            if (!isset($phrases[$phrase])) {
                $toSave[] = $phrase;
            }
        }

        $res = true;
        if (!empty($toSave)) {
            foreach ($toSave as $phrase) {
                $wordException = new WordException([
                    'shop_id' => $this->shop->id,
                    'word' => $phrase
                ]);

                $res &= $wordException->save();
            }
        }

        return $res;
    }
}

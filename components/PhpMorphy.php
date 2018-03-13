<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 09.10.16
 * Time: 17:46
 */

namespace app\components;

use yii\base\Component;

/**
 * Обертка над phpMorphy
 *
 * Class PhpMorphy
 * @package app\components
 */
class PhpMorphy extends Component
{
    /**
     * @var string
     */
    public $dictPath;

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var \phpMorphy
     */
    private $phpMorphy;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $dictBundle = new \phpMorphy_FilesBundle(\Yii::getAlias($this->dictPath), 'rus');
        $this->phpMorphy = new \phpMorphy($dictBundle, $this->options);
    }

    /**
     * @param mixed $parts
     * @return array
     */
    public function getOrderedBaseForms($parts)
    {
        if (empty($parts)) {
            return [];
        }

        $partsMap = array_combine((array)$parts, array_map('mb_strtoupper', (array)$parts));
        $forms = $this->getBaseForm(array_values($partsMap));

        $result = [];
        foreach ((array)$partsMap as $originalPart => $part) {
            if (empty($forms[$part])) {
                $result[$originalPart] = $part;
            } else {
                $result[$originalPart] = $forms[$part];
            }
        }

        return $result;
    }

    /**
     * @param mixed $parts
     * @return array
     */
    public function getOrderedBaseFormsList($parts)
    {
        if (empty($parts)) {
            return [];
        }

        $result = [];
        foreach ((array)$parts as $part) {
            if (preg_match('#[^а-яА-Я-]#ui', $part)) {
                $forms = null;
            } else {
                $forms = $this->getBaseForm(mb_strtoupper($part));
            }

            if (!empty($forms)) {
                $result[] = ['original' => $part, 'base' => (array)$forms];
            } else {
                $result[] = ['original' => $part, 'base' => (array)mb_strtoupper($part)];
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function __call($name, $params)
    {
        return call_user_func_array([$this->phpMorphy, $name], $params);
    }
}

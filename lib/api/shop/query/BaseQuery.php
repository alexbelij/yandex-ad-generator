<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 08.03.16
 * Time: 22:23
 */

namespace app\lib\api\shop\query;

use app\lib\api\shop\dataSource\DataSourceInterface;
use app\lib\api\shop\query\translator\TranslatorFactory;
use yii\base\Object;
use yii\helpers\Inflector;

class BaseQuery extends Object implements QueryInterface
{
    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var
     */
    protected $page;

    /**
     * @var string
     */
    protected $order;

    /**
     * @param int $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param int $page
     * @return $this
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param string $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuery(DataSourceInterface $dataSource)
    {
        $vars = array_filter(
            get_object_vars($this),
            function ($value) {
                return !is_null($value);
            }
        );
        $result = [];
        foreach ($vars as $key => $value) {
            $result[Inflector::underscore($key)] = $value;
        }

        return $this->getTranslator($dataSource)->translate($result);
    }

    /**
     * @param DataSourceInterface $dataSource
     * @return translator\TranslatorInterface
     */
    public function getTranslator(DataSourceInterface $dataSource)
    {
        return TranslatorFactory::create($dataSource);
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 08.10.16
 * Time: 14:55
 */

namespace app\lib\import\yml\strategies\defaultStrategy;

use app\components\LoggerInterface;
use app\lib\import\yml\AbstractTagParser;
use app\lib\import\yml\strategies\entity\Category;
use app\models\ExternalCategory;
use app\models\FileImport;
use yii\base\Exception;

/**
 * Class CategoryTagParser
 * @package app\lib\import\yml\strategies\categoryBrandStrategy
 */
class CategoryTagParser extends AbstractTagParser
{
    /**
     * @var CategoriesTagParser
     */
    protected $categoriesTagParser;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var string
     */
    protected $categoryName = '';

    /**
     * @inheritDoc
     */
    public function __construct(FileImport $fileImport, LoggerInterface $logger, AbstractTagParser $tagParser)
    {
        parent::__construct($fileImport, $logger);
        $this->categoriesTagParser = $tagParser;
    }

    /**
     * @inheritDoc
     */
    public function parseAttributes($tagName, $attributes)
    {
        if (empty($attributes['ID'])) {
            throw new Exception('Missing category id');
        }

        $id = $attributes['ID'];
        $parentId = !empty($attributes['PARENTID']) ? $attributes['PARENTID'] : null;

        $this->logger->log("Start parse category...");
        $this->logger->log("Outer id: $id");

        $this->category = new Category([
            'id' => $id,
            'parentId' => $parentId
        ]);
    }

    /**
     * @inheritDoc
     */
    public function parseCharacters($tagName, $data)
    {
        $data = trim($data);
        if (!empty($data)) {
            $this->categoryName .= $data;
        }
    }

    /**
     * @inheritDoc
     */
    public function end($tagName)
    {
        $this->category->title = $this->categoryName;
        $this->categoriesTagParser->addCategory($this->category);
    }
}

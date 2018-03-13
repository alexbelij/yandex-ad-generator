<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 24.08.16
 * Time: 10:41
 */

namespace app\lib\import\yml\strategies\defaultStrategy;
use app\lib\import\yml\AbstractTagParser;

/**
 * Class YmlCatalog
 * @package app\lib\import\yml
 */
class YmlCatalogTagParser extends AbstractTagParser
{
    /**
     * @inheritDoc
     */
    public function parseAttributes($name, $attributes)
    {
        if (!empty($attributes['DATE'])) {
            $this->fileImport->catalog_date = date('Y-m-d H:i:s', strtotime($attributes['DATE']));
        }
    }

    /**
     * @inheritDoc
     */
    public function parseCharacters($name, $data)
    {
        // TODO: Implement parseCharacters() method.
    }
}

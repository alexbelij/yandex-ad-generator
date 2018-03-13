<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 24.08.16
 * Time: 11:02
 */

namespace app\lib\import\yml\strategies\defaultStrategy;

use app\lib\import\yml\AbstractTagParser;

class ShopTagParser extends AbstractTagParser
{
    /**
     * @inheritDoc
     */
    public function parseAttributes($tagName, $attributes)
    {

    }

    /**
     * @inheritDoc
     */
    public function parseCharacters($name, $data)
    {
        $data = trim($data);
        if ($name == 'company' && !empty($data)) {
            $this->fileImport->company_name = $data;
        }
    }
}

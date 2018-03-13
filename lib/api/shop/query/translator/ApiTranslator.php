<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 28.08.16
 * Time: 8:13
 */

namespace app\lib\api\shop\query\translator;


/**
 * Class ApiTranslator
 * @package app\lib\api\shop\query\translator
 */
class ApiTranslator implements TranslatorInterface
{
    /**
     * @param array $params
     * @return array
     */
    public function translate(array $params)
    {
        foreach ($params as $key => $value) {
            $params[$key] = is_array($value) ? implode(',', $value) : $value;
        }

        $this->applyOrder($params);

        return $params;
    }

    /**
     * @param array $params
     */
    protected function applyOrder(array &$params)
    {
        if (!empty($params['order'])) {
            $orderStr = $params['order'];
            unset($params['order']);
            $matches = [];
            $orderParts = [];
            preg_match_all('#(\w+)\s+(ASC|DESC)#i', $orderStr, $matches);
            if (!empty($matches)) {
                foreach ($matches[1] as $key => $fieldName) {
                    $orderParts[] = (strtolower($matches[2][$key]) == 'asc' ? '+' : '-') . $fieldName;
                }
            }
            $params['order'] = implode(',', $orderParts);
        }
    }
}

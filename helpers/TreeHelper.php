<?php

namespace app\helpers;

/**
 * Class TreeHelper
 * @package app\helpers
 */
class TreeHelper
{
    /**
     * Возвращает массив для построения дерева категорий
     *
     * @param array $categories
     * @param array $selectedCategoriesIds
     * @param bool $selectAllIfEmpty
     * @return array
     */
    public static function getCategoriesTree(
        array $categories, array $selectedCategoriesIds = [], $selectAllIfEmpty = false
    ) {
        $result = [];
        $categoryIds = ArrayHelper::getColumn($categories, 'id');

        foreach ($categories as $category) {

            if (!in_array($category['parent_id'], $categoryIds)) {
                $category['parent_id'] = null;
            }

            $result[] = [
                'id' => (string)$category['id'],
                'text' => $category['title'],
                'parent' => $category['parent_id'] ? (string) $category['parent_id'] : '-1',
                'state' => [
                    'selected' => is_array($selectedCategoriesIds) && in_array($category['id'], $selectedCategoriesIds)
                ]
            ];
        }

        array_unshift($result, [
            'id' => '-1',
            'text' => 'Все',
            'parent' => '#',
            'state' => [
                'selected' => $selectAllIfEmpty && empty($selectedCategoriesIds) ? true : false
            ]
        ]);

        return $result;
    }
}

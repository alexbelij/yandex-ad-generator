<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 12.11.16
 * Time: 17:13
 */

namespace app\lib\import\yml\extensions;
use app\lib\import\yml\strategies\entity\Offer;
use app\models\WordException;

/**
 * Class ColorsFromOfferExtension
 * @package app\lib\import\yml\extensions
 */
class ColorsFromOfferExtension implements ExtensionInterface
{
    /**
     * @inheritdoc
     */
    public function run(ExtensionItemDto $item)
    {
        /** @var Offer $offer */
        $offer = $item->data;

        /** @var WordExceptionSaver $saver */
        $saver = $item->extra['saver'];
        if (empty($offer->description)) {
            return false;
        }

        $matches = [];
        $colors = [];
        if (preg_match_all('#Цвет\b[^-,]+-\s+([\w\s,]+)(?=[А-Я])#u', $offer->description, $matches)) {
            foreach ($matches[1] as $matchColor) {
                $matchColor = trim($matchColor, ' ,');
                $colors = array_merge($colors, explode(',', $matchColor));
            }
            $colors = array_unique(array_map('trim', $colors));
        }

        if (!empty($colors)) {
            if (preg_match_all('#Тип поверхности\b[^-,]+-\s+([\w\s,]+)(?=[А-Я])#u', $offer->description, $matches)) {
                $colorSurfaces = [];
                foreach ($matches[1] as $matchColor) {
                    $matchColor = trim($matchColor, ' ,');
                    $colorSurfaces = array_merge($colorSurfaces, explode(',', $matchColor));
                }
                $colorSurfaces = array_unique(array_map('trim', $colorSurfaces));
                foreach ($colors as $color) {
                    foreach ($colorSurfaces as $colorSurface) {
                        $colors[] = $color . ' ' . $colorSurface;
                    }
                }
            }
        }

        if (!empty($colors)) {
            foreach ($colors as $color) {
                $saver->addPhrase($color);
            }
        }

        return true;
    }
}

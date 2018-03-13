<?php

namespace app\helpers;

/**
 * Class StringHelper
 * @package app\helpers
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class StringHelper extends \yii\helpers\StringHelper
{
    /**
     * @param string $str
     * @param string $delimiter
     * @return array
     */
    public static function explodeByDelimiter($str, $delimiter = '\r\n|\n')
    {
        return array_map('trim', preg_split("#({$delimiter})#", $str));
    }

    /**
     * Бинарно безопасное сравнение строк
     *
     * @param $str1
     * @param $str2
     * @param null $encoding
     * @return int
     */
    public static function strCaseCmp($str1, $str2, $encoding = null)
    {
        if (null === $encoding) { $encoding = mb_internal_encoding(); }
        return strcmp(mb_strtoupper($str1, $encoding), mb_strtoupper($str2, $encoding));
    }

    /**
     * Преобразовать первый символ строки в верхний регистр, работает с utf
     *
     * @param string $string
     * @return string
     */
    public static function mbUcFirst($string)
    {
        $fc = mb_strtoupper(mb_substr($string, 0, 1));
        return $fc . mb_substr($string, 1);
    }

    /**
     * Обрезка по символам до слов
     *
     * @param string $string
     * @param int $length
     * @param bool $fromEnd
     * @return string
     */
    public static function truncateByWords($string, $length, $fromEnd = false)
    {
        return $fromEnd ?
            self::truncateByWordsFromEnd($string, $length) :
            self::truncateByWordsDirect($string, $length);
    }

    /**
     * @param $string
     * @param $length
     * @return string
     */
    public static function truncateByWordsDirect($string, $length)
    {
        $stringLength = mb_strlen($string);
        $words = [];
        $word = '';
        $totalLength = 0;

        for ($i = 0; $i < $stringLength; $i++) {
            $ch = mb_substr($string, $i, 1);
            if ($ch == ' ' && !empty($word)) {
                $words[] = $word;
                $word = '';
            } else {
                $word .= $ch;
            }

            if ($totalLength > $length) {
                return implode(' ', $words);
            }
            $totalLength++;
        }

        return implode(' ', $words);
    }

    /**
     * @param $string
     * @param $length
     * @return string
     */
    public static function truncateByWordsFromEnd($string, $length)
    {
        $stringLength = mb_strlen($string);
        $words = [];
        $word = '';
        $totalLength = 1;

        for ($i = $stringLength - 1; $i >= 0; $i--) {
            $ch = mb_substr($string, $i, 1);

            if ($i == 0) {
                $word .= $ch;
                $ch = ' ';
            }

            if (($ch == ' ' && !empty($word))) {
                $words[] = $word;
                $word = '';
            } else {
                $word .= $ch;
            }

            if ($totalLength >= $length) {
                if ($stringLength > $totalLength && mb_substr($string, $i - 1, 1) == ' ') {
                    $words[] = $word;
                }

                return implode(array_reverse(self::toArray(implode(' ', $words))));
            }
            $totalLength++;
        }

        return implode(array_reverse(self::toArray(implode(' ', $words))));
    }

    /**
     * @param string $string
     * @return array
     */
    public static function toArray($string)
    {
        return preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 03.09.16
 * Time: 10:57
 */

namespace app\helpers;
use yii\base\Exception;
use yii\helpers\FileHelper;

/**
 * Class RemoteFileHelper
 * @package app\helpers
 */
class RemoteFileHelper
{
    /**
     * @param string $path
     * @param int $size
     * @return string
     * @throws Exception
     */
    public static function readPart($path, $size)
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 2,
                'max_redirects' => 5
            ]
        ]);

        $fh = fopen($path, 'r', false, $context);

        return fread($fh, $size);
    }

    /**
     * @param string $url
     * @param null|string $path
     * @return string
     * @throws Exception
     */
    public static function downloadFile($url, $path = null)
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 10,
                'max_redirects' => 5
            ]
        ]);

        if (!$path) {
            $path = \Yii::getAlias('@app/uploads/');
        }

        $fSource = fopen($url, 'rb', false, $context);

        if ($fSource === false) {
            throw new Exception("Ошибка при четнии файла - $url");
        }

        $extension = self::getExtension($url);
        $targetFileName = rtrim($path, '/') . '/' . uniqid();
        if ($extension) {
            $targetFileName .= ".$extension";
        }
        $fTarget = fopen($targetFileName, 'w');

        while (!feof($fSource)) {
            $content = fread($fSource, 8192);
            fwrite($fTarget, $content);
        }

        fclose($fTarget);
        fclose($fSource);

        return $targetFileName;
    }

    /**
     * @param string $path
     * @return bool|string
     */
    public static function getExtension($path)
    {
        $pos = strrpos($path, '.');
        if ($pos == false) {
            return false;
        }

        $ext = substr($path, $pos + 1);

        if (preg_match('#[^\w]#', $ext)) {
            $ext = preg_split('#[^\w]#', $ext, 2);
            return $ext[0];
        }

        return $ext;
    }
}

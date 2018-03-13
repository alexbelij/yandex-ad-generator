<?php

namespace app\lib\tasks;

use app\helpers\ArrayHelper;
use app\models\ExternalProduct;

/**
 * Class ProductValidateTask
 * @package app\lib\tasks
 */
class ProductValidateTask extends AbstractTask
{
    const TASK_NAME = 'ProductValidate';

    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $query = ExternalProduct::find()
            ->andWhere([
                'is_available' => 1,
                'shop_id' => $this->task->shop_id,
            ])
            ->orderBy('id');

        $logger = $this->getLogger();
        $logger->log("Начинаем проверку ссылок для магазина: {$this->task->shop->name}");

        $totalCountQuery = clone $query;
        $totalCount = $totalCountQuery->count();
        $count = 0;

        foreach ($query->batch(2000) as $products) {
            /** @var ExternalProduct $product */
            foreach ($products as $product) {
                $count++;
                $logger->log('Проверка ссылки: ' . $product->url);
                if ($this->isValidUrl($product->url)) {
                    //$httpInfo = $this->getHttpInfo($product->url);
                    $tryCount = 0;

                    do {
                        try {
                            $headers = $this->getHeaders($product->url);
                        } catch (\Exception $e) {
                            $headers = false;
                        }

                        $tryCount++;

                    } while ($headers == false && $tryCount < 3);


                    if ($headers) {
                        $logger->log('Получены заголовки: ' . json_encode($headers));
                    } else {
                        $logger->log('Ошибка получения заголовков');
                    }

                    if (!empty($headers[0]) && preg_match('#200#', $headers[0])) {
                        $product->is_url_available = true;
                        $logger->log('Ссылка валидна');
                    } else {
                        $logger->log('Ссылка недоступна');
                        $product->is_url_available = false;
                    }
                } else {
                    $product->is_url_available = false;
                }

                if ($count % 10 == 0 || $totalCount == $count) {
                    $this->task->info = "$count / $totalCount";
                    $this->task->save();
                }

                $product->available_check_at = date('Y-m-d H:i:s');
                $product->save();
            }
        }
    }

    /**
     * Выполняет запрос и проверяет код ответа
     *
     * @param string $url
     * @return bool
     */
    protected function getHttpInfo($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');

        if (!curl_exec($ch)) {
            return null;
        }

        $info = curl_getinfo($ch);
        curl_close($ch);

        return $info;
    }

    /**
     * @param string $url
     * @param int $redirectCount
     * @return array|bool|null
     */
    protected function getHeaders($url, $redirectCount = 0)
    {
        if ($redirectCount > 4) {
            return false;
        }
        $url = trim($url, '?');
        $urlParts = parse_url($url);
        $errno = '';
        $errstr = '';
        $fsock = fsockopen($urlParts['host'], ArrayHelper::getValue($urlParts, 'port', 80), $errno, $errstr);
        stream_set_timeout($fsock, 6);

        if (!$fsock) {
            return null;
        }

        $path = ArrayHelper::getValue($urlParts, 'path', '/');
        if (!empty($urlParts['query'])) {
            $path .= '?' . $urlParts['query'];
        }

        $out = "GET $path HTTP/1.1\r\n";
        $out .= "Host: {$urlParts['host']}\r\n";
        $out .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n";
        $out .= "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36\r\n";
        $out .= "Accept-Language:ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4\r\n";
        $out .= "Connection: Close\r\n\r\n";
        fwrite($fsock, $out);

        $headers = [];
        while (!feof($fsock)) {
            $header = fgets($fsock, 1024);
            if (strpos($header, "\r\n") === 0 || strpos($header, "\r\n\r\n") !== false) {
                fclose($fsock);
                break;
            }
            $headers[] = $header;
            if (preg_match('#location#i', $header)) {
                $redirect = preg_replace("/location:/i", "", $header);
                $redirect = trim($redirect);
                $redirectUrlParts = parse_url($redirect);
                $scheme = ArrayHelper::getValue($redirectUrlParts, 'scheme', 'http');

                $redirectUrl = "$scheme://" . ArrayHelper::getValue($redirectUrlParts, 'host', $urlParts['host']) .
                    ArrayHelper::getValue($redirectUrlParts, 'path', '/') . '?' .
                    ArrayHelper::getValue($redirectUrlParts, 'query', '');

                fclose($fsock);

                return $this->getHeaders($redirectUrl, $redirectCount + 1);
            }
        }

        return $headers;
    }

    /**
     * @param string $url
     * @return bool
     */
    protected function isValidUrl($url)
    {
        return !empty($url) && preg_match('#^https?#', $url);
    }

    /**
     * @inheritDoc
     */
    protected function getLogFileName()
    {
        return 'validate_link_' . $this->task->id;
    }
}

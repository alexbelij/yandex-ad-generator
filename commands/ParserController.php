<?php

namespace app\commands;

use yii\console\Controller;
use yii\helpers\ArrayHelper;
use Zend\Http\Client;

/**
 * Class ParserController
 * @package app\commands
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class ParserController extends Controller
{
    const START_URL = 'http://zmost.ru/vse-voprosy/';

    /**
     * @var string
     */
    private $host = 'http://zmost.ru';

    /**
     * Список найденных ссылок в процессе парсинга
     * @var array
     */
    private $foundLinks = [];

    /**
     * Список ссылок на парсинг
     * @var array
     */
    private $linkToParse = [];

    /**
     * Запуск парсинга
     */
    public function actionParse()
    {
        $this->linkToParse[] = self::START_URL;

        $fh = fopen('/home/den/answer.csv', 'a');
        fputcsv($fh, ['Вопрос', 'Ответ', 'Тема', 'Город', 'Автор'], ';');

        while (1) {
            if (!count($this->linkToParse)) {
                break;
            }
            $link = array_shift($this->linkToParse);

            $url = $this->normalizeUrl($link);

            if (!$this->isValidUrl($url)) {
                continue;
            }

            echo 'start parse url: ' . $url . PHP_EOL;
            $response = $this->getResponse($url);
            if (!$response || $response->getStatusCode() !== 200) {
                continue;
            }

            $result = $this->parse($response->getBody());
            foreach ($result as $item) {
                if (empty($item['question'])) {
                    echo 'skipped' . PHP_EOL;
                    continue;
                }
                fputcsv($fh, [
                    ArrayHelper::getValue($item, 'question'),
                    ArrayHelper::getValue($item, 'answer'),
                    ArrayHelper::getValue($item, 'category'),
                    ArrayHelper::getValue($item, 'city'),
                    ArrayHelper::getValue($item, 'author')
                ], ';');
            }
        }
    }

    /**
     * @param string $link
     * @return string
     */
    protected function normalizeUrl($link)
    {
        if (strpos($link, 'http') === false) {
            $url = $this->host . '/' . ltrim($link, '/');
        } else {
            $url = $link;
        }

        return $url;
    }

    /**
     * @param string $url
     * @return bool
     */
    protected function isValidUrl($url)
    {
        return strpos($url, $this->host) === 0;
    }

    /**
     * @param $content
     * @return array
     */
    protected function parse($content)
    {
        $xpath = $this->createXPath($content);

        $result = [];
        $blockNodes = $xpath->query("//div[@class='items-row']");
        foreach ($blockNodes as $blockNode) {
            $item = [];
            $categoryStr = strip_tags(
                $xpath->query(".//dd[@class='category-name']", $blockNode)->item(0)->nodeValue
            );
            $item['category'] = trim(mb_substr($categoryStr, mb_strpos($categoryStr, ':') + 1));

            $questionNode = $xpath->query(".//p[@class='vopros']", $blockNode);

            if (!$questionNode->length) {
                continue;
            }

            $detailsNode = $xpath->query(".//dd[@class='hits']/a", $blockNode);
            if (!$detailsNode->length) {
                continue;
            }

            $detailsUrl = $detailsNode->item(0)->getAttribute('href');
            if (!$detailsUrl) {
                continue;
            }

            $detailsUrl = $this->normalizeUrl($detailsUrl);

            $detailsInfo = $this->parseDetails($detailsUrl);
            if (empty($detailsInfo)) {
                continue;
            }
            $item = array_merge($item, $detailsInfo);

            $result[] = $item;
        }

        $this->findAndSaveLinks($xpath);

        return $result;
    }

    /**
     * @param $content
     * @return \DOMXPath
     */
    protected function createXPath($content)
    {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($content);
        libxml_use_internal_errors(false);

        return new \DOMXPath($doc);
    }

    /**
     * @param string $detailsUrl
     * @return array|null
     */
    protected function parseDetails($detailsUrl)
    {
        $response = $this->getResponse($detailsUrl);

        if (!$response || $response->getStatusCode() != 200) {
            return null;
        }

        $content = $response->getBody();
        $xpath = $this->createXPath($content);

        $questionNode = $xpath->query("//p[@class='vopros']");

        if (!$questionNode->length) {
            return null;
        }

        $question = trim($questionNode->item(0)->nodeValue);
        if (!$question) {
            return null;
        }

        $item = [];
        $matches = [];
        $question = preg_replace("#\r\n|\n#", ' ', $question);
        if (preg_match('#(\[([^\]]*)\])?\s*Вопрос:\s*(\[([^\]]*)\])?(.*)#um', $question, $matches)) {
            $item['author'] = trim($matches[2]);
            $item['city'] = trim($matches[4]);
            $item['question'] = trim($matches[5]);
        }

        $answerNodes = $xpath->query("//div[@class='otvet']");
        if (!$answerNodes) {
            return $item;
        }

        $answer = $answerNodes->item(0)->ownerDocument->saveHTML($answerNodes->item(0));
        $item['answer'] = trim(strip_tags(preg_replace('#<div\s+(?!class="otvet")[^>]*>(\s|.)*?</div>#imu', '', $answer), '<a>'));

        return $item;
    }

    /**
     * @param \DOMXPath $xpath
     */
    protected function findAndSaveLinks(\DOMXPath $xpath)
    {
        $linksNodes = $xpath->query('//*[@id="pageright2"]/div[6]/div[1]/ul/li/a');
        /** @var \DOMNode $linkNode */
        foreach ($linksNodes as $linkNode) {
            if (ctype_digit($linkNode->nodeValue)) {
                $link = $linkNode->getAttribute('href');
                if ($linkNode->nodeValue < 886) {
                    continue;
                }
                if (!in_array($link, $this->foundLinks)) {
                    $this->foundLinks[] = $link;
                    $this->linkToParse[] = $link;
                }
            }
        }
    }

    /**
     * @param string $url
     * @return \Zend\Http\Response
     */
    protected function getResponse($url)
    {
        $client = new Client();
        $client->setUri($url);
        $client->setOptions([
            'timeout' => 30
        ]);

        try {
            return $client->send();
        } catch (\Exception $e) {
            return null;
        }
    }
}

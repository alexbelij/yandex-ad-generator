<?php

namespace app\lib\api\yandex\direct;

use app\lib\api\auth\ApiIdentityInterface;
use app\lib\api\yandex\direct\exceptions\ConnectionException;
use app\lib\api\yandex\events\RequestEvent;
use yii\base\Component;
use yii\base\Event;
use Zend\Http\Client;

/**
 * Class Connection
 * @package app\lib\api\yandex\direct
 */
class Connection extends Component
{
    const EVENT_AFTER_REQUEST = 'after_request';

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * @var ApiIdentityInterface
     */
    protected $authIdentity;

    /**
     * @var int
     */
    protected $timeout = 10;

    /**
     * Connection constructor.
     * @param ApiIdentityInterface $authIdentity
     * @param null $apiUrl
     * @param array $config
     */
    public function __construct(ApiIdentityInterface $authIdentity = null, $apiUrl = null, $config = [])
    {
        parent::__construct($config);
        if (is_null($apiUrl)) {
            $this->apiUrl = \Yii::$app->params['yandex']['apiUrl'];
        } else {
            $this->apiUrl = $apiUrl;
        }
        $this->authIdentity = $authIdentity;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setApiUrl($url)
    {
        $this->apiUrl = $url;
        return $this;
    }

    /**
     * @param ApiIdentityInterface $token
     * @return $this
     */
    public function setAuthIdentity(ApiIdentityInterface $token)
    {
        $this->authIdentity = $token;
        return $this;
    }

    /**
     * @param string $resource
     * @param array $params
     * @param string $method
     * @return mixed
     * @throws ConnectionException
     */
    public function query($resource, array $params = [], $method = 'get')
    {
        $uri = rtrim($this->apiUrl, '/') . '/' . strtolower($resource);

        $jsonParams = json_encode([
            'method' => $method,
            'params' => $params
        ]);

        if (empty($this->authIdentity)) {
            throw new ConnectionException('Не передан токен');
        }

        $response = $this->getHttpClient()
            ->setHeaders($this->getHeaders())
            ->setMethod('POST')
            ->setUri($uri)
            ->setAdapter('Zend\Http\Client\Adapter\Curl')
            ->setRawBody($jsonParams)
            ->send();

        if (!$response->isSuccess()) {
            throw (new ConnectionException($response->getReasonPhrase(), $response->getStatusCode()))
                ->setDetails($jsonParams);
        }

        $this->trigger(self::EVENT_AFTER_REQUEST, new RequestEvent([
            'response' => $response,
            'account' => $this->authIdentity->getAccount()
        ]));

        $body = $response->getBody();

        $result = json_decode($body, true);

        if (!is_array($result)) {
            throw new ConnectionException(json_last_error_msg());
        }

        if (!empty($result['error'])) {
            $message = $result['error']['error_string'] . ', details: ' . $result['error']['error_detail'];
            $message .= ' Токен:' . $this->authIdentity->getToken() . ', аккаунт: ' .
                $this->authIdentity->getAccount()->title;
            throw new ConnectionException($message, $result['error']['error_code']);
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getHeaders()
    {
        $headers = [
            'Accept-Language' => 'ru',
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36',
            'Content-type' => 'application/json'
        ];
        if ($this->authIdentity) {
            $headers['Authorization'] = 'Bearer ' . $this->authIdentity->getToken();
        }

        return $headers;
    }

    /**
     * @return Client
     */
    protected function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new Client(null, ['timeout' => $this->timeout]);
        }

        return $this->httpClient->reset();
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return Connection
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }
}

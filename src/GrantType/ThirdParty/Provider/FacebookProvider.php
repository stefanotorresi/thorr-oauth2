<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\GrantType\ThirdParty\Provider;

use Thorr\OAuth\Entity\User;
use Zend\Http\Client;
use Zend\Http\Response;
use Zend\Json\Json;

class FacebookProvider implements
    ProviderInterface
{
    use ProviderTrait;

    /**
     * @var string
     */
    protected $uri;
    /**
     * @var string
     */
    protected $appId;

    protected $clientOptions;

    /**
     * @param $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options)
    {
        if (! isset($options['app_id'])) {
            throw new Exception\InvalidArgumentException('Missing "app_id" option');
        }

        $this->setAppId($options['app_id']);

        if (! isset($options['uri'])) {
            throw new Exception\InvalidArgumentException('Missing "uri" option');
        }

        $this->setUri($options['uri']);

        if (! isset($options['user_factory'])) {
            $options['user_factory'] = function ($data) {
                return new User($data->id);
            };
        }

        $this->setUserFactory($options['user_factory']);

        if (isset($options['client_options'])) {
            $this->clientOptions = $options['client_options'];
        }
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param string $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return 'facebook';
    }

    /**
     * @param $userId
     * @param $accessToken
     * @return bool
     * @throws Exception\ClientException
     */
    public function validate($userId, $accessToken)
    {
        $client = new Client($this->uri, $this->clientOptions);
        $client->setMethod('POST');
        $client->setParameterPost([
            'access_token' => $accessToken,
            'batch' => Json::encode([
                [
                    'method' => 'GET',
                    'relative_url' => 'debug_token?' . http_build_query([
                        'access_token' => $accessToken,
                        'input_token' => $accessToken,
                    ])
                ],
                [
                    'method' => 'GET',
                    'relative_url' => 'me'
                ]
            ]),
        ]);

        $batchBody = $this->decodeBody($client->send());

        $tokenInfo = Json::decode($batchBody[0]->body);

        if (! isset($tokenInfo->data->app_id) || ! isset($tokenInfo->data->user_id)) {
            throw new Exception\ClientException("Invalid data returned by provider", 422);
        }

        if ($tokenInfo->data->app_id !== $this->appId || $tokenInfo->data->user_id !== $userId) {
            return false;
        }

        $this->userData = Json::decode($batchBody[1]->body);

        return true;
    }

    /**
     * @param Response $response
     * @return mixed
     * @throws Exception\ClientException
     */
    protected function decodeBody(Response $response)
    {
        if ($response->isServerError()) {
            throw new Exception\ClientException(sprintf(
                "'%s' provider encountered a '%s' error while querying '%s'",
                $this->getIdentifier(),
                $response->getReasonPhrase(),
                $this->uri
            ));
        }

        $body = Json::decode($response->getBody());

        if ($response->isClientError()) {
            throw new Exception\ClientException($body->error->message, $response->getStatusCode());
        }

        return $body;
    }
}

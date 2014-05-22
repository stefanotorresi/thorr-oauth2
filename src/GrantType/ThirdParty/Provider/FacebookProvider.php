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

    /**
     * @var array
     */
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
        $this->uri = rtrim($uri, '/');
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
     * @param null $errorMessage
     * @throws Exception\ClientException
     * @return bool
     */
    public function validate($userId, $accessToken, &$errorMessage = null)
    {
        $client = new Client($this->uri . '/me', $this->clientOptions);
        $client->setMethod('GET');
        $client->setParameterGet(['access_token' => $accessToken]);

        $userData = $this->decodeBody($client->send());

        if (isset($userData->error)) {
            throw new Exception\ClientException($userData->error->message, 400);
        }

        if (! isset($userData->id)) {
            throw new Exception\ClientException("Invalid data returned by provider", 400);
        }

        if ($userData->id !== $userId) {
            $errorMessage = 'user_id mismatch';
            return false;
        }

        $this->userData = $userData;

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

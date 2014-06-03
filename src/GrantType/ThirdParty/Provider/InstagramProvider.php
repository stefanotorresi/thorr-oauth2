<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\GrantType\ThirdParty\Provider;

use Zend\Http\Client;
use Zend\Http\Response;
use Zend\Json\Json;

class InstagramProvider implements ProviderInterface
{
    use ProviderTrait;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var string
     */
    protected $clientId;

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
        if (! isset($options['client_id'])) {
            throw new Exception\InvalidArgumentException('Missing "client_id" option');
        }

        $this->setClientId($options['client_id']);

        if (! isset($options['uri'])) {
            throw new Exception\InvalidArgumentException('Missing "uri" option');
        }

        $this->setUri($options['uri']);

        if (isset($options['client_options'])) {
            $this->clientOptions = $options['client_options'];
        }
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return 'instagram';
    }

    /**
     * @param $userId
     * @param $accessToken
     * @param $errorMessage
     * @throws Exception\ClientException
     * @return boolean
     */
    public function validate($userId, $accessToken, &$errorMessage = null)
    {
        $client = new Client($this->uri . '/users/self', $this->clientOptions);
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

        $this->userId = $userData->id;
        unset($userData->id);
        $userData->token = $accessToken;

        $this->userData = (array) $userData;

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
            throw new Exception\ClientException($body->meta->error_message, $response->getStatusCode());
        }

        return $body->data;
    }

    /**
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param array $clientOptions
     */
    public function setClientOptions($clientOptions)
    {
        $this->clientOptions = $clientOptions;
    }

    /**
     * @return array
     */
    public function getClientOptions()
    {
        return $this->clientOptions;
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
    public function getUri()
    {
        return $this->uri;
    }
}

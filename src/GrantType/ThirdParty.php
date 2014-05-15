<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\GrantType;

use InvalidArgumentException;
use OAuth2\GrantType\GrantTypeInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\ResponseType\AccessTokenInterface;
use Thorr\OAuth\Entity\ScopesProviderInterface;
use Thorr\OAuth\Entity\UserInterface;
use Thorr\OAuth\GrantType\ThirdParty\Provider\Exception\ClientException;
use Thorr\OAuth\GrantType\ThirdParty\Provider\ProviderInterface;
use Thorr\OAuth\Storage\ThirdPartyStorageInterface;
use Traversable;
use Zend\Stdlib\Guard\ArrayOrTraversableGuardTrait;

class ThirdParty implements GrantTypeInterface
{
    use ArrayOrTraversableGuardTrait;

    /**
     * @var ThirdPartyStorageInterface
     */
    protected $storage;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var array|Traversable
     */
    protected $providers;

    /**
     * @param ThirdPartyStorageInterface $storage
     * @param array|Traversable $providers
     */
    public function __construct(ThirdPartyStorageInterface $storage, $providers = [])
    {
        $this->storage = $storage;

        if (! empty($providers)) {
            $this->setProviders($providers);
        }
    }

    public function getQuerystringIdentifier()
    {
        return 'third_party';
    }

    public function validateRequest(RequestInterface $request, ResponseInterface $response)
    {
        $userId              = $request->request("user_id");
        $providerName        = $request->request("provider");
        $providerAccessToken = $request->request("provider_access_token");

        if (! $userId || ! $providerName || ! $providerAccessToken) {
            $response->setError(
                400,
                'invalid_request',
                'Missing parameters: "username", "provider" and "provider_access_token" are required'
            );

            return false;
        }

        if (! isset($this->providers[$providerName])) {
            $response->setError(400, 'invalid_request', 'Unknown provider selected');

            return false;
        }

        /** @var ProviderInterface $provider */
        $provider = $this->providers[$providerName];

        try {
            $errorMessage = '';
            if (! $provider->validate($userId, $providerAccessToken, $errorMessage)) {
                $response->setError(401, 'invalid_grant', 'Invalid third party credentials: '.$errorMessage);

                return false;
            }
        } catch (ClientException $e) {
            $response->setError($e->getCode(), 'provider_error', $e->getMessage());

            return false;
        }

        $this->user = $provider->getUser();

        if (! $this->storage->hasUser($this->user)) {
            $this->storage->saveUser($this->user);
        }

        return true;
    }

    /**
     * not actually needed
     * client_id is retrieved via a ClientAssertionTypeInterface before querying this grant.
     */
    public function getClientId()
    {
        return null;
    }

    /**
     * @return mixed|null
     */
    public function getUserId()
    {
        if (! $this->user) {
            return null;
        }

        return $this->user->getId();
    }

    public function getScope()
    {
        return $this->user instanceof ScopesProviderInterface ? $this->user->getScopesString() : null;
    }

    /**
     * @param AccessTokenInterface $accessToken
     * @param $client_id
     * @param $user_id
     * @param $scope
     * @return mixed
     */
    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        return $accessToken->createAccessToken($client_id, $user_id, $scope);
    }

    /**
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param array|Traversable $providers
     * @throws InvalidArgumentException
     */
    public function setProviders($providers)
    {
        $this->guardForArrayOrTraversable($providers);

        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

    /**
     * @param ProviderInterface $provider
     */
    public function addProvider(ProviderInterface $provider)
    {
        $this->providers[$provider->getIdentifier()] = $provider;
    }
}

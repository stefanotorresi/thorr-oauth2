<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\GrantType;

use Exception;
use InvalidArgumentException;
use OAuth2\GrantType\GrantTypeInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\ResponseType\AccessTokenInterface as AccessTokenFactory;
use Thorr\OAuth2\DataMapper\ThirdPartyMapperInterface;
use Thorr\OAuth2\DataMapper\TokenMapperInterface;
use Thorr\OAuth2\Entity;
use Thorr\OAuth2\GrantType\ThirdParty\Provider\Exception\ClientException;
use Thorr\OAuth2\GrantType\ThirdParty\Provider\ProviderInterface;
use Thorr\OAuth2\Options\ModuleOptions;
use Thorr\Persistence\DataMapper\DataMapperInterface;
use Traversable;
use Zend\Stdlib\Guard\ArrayOrTraversableGuardTrait;

class ThirdPartyGrantType implements GrantTypeInterface
{
    use ArrayOrTraversableGuardTrait;

    /**
     * @var DataMapperInterface
     */
    protected $userMapper;

    /**
     * @var ThirdPartyMapperInterface
     */
    protected $thirdPartyMapper;

    /**
     * @var TokenMapperInterface
     */
    protected $accessTokenMapper;

    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;

    /**
     * @var Entity\UserInterface
     */
    protected $user;

    /**
     * @var array|Traversable
     */
    protected $providers;

    /**
     * @param DataMapperInterface $userMapper
     * @param ThirdPartyMapperInterface $thirdPartyMapper
     * @param TokenMapperInterface $accessTokenMapper
     * @param ModuleOptions $moduleOptions
     */
    public function __construct(
        DataMapperInterface $userMapper,
        ThirdPartyMapperInterface $thirdPartyMapper,
        TokenMapperInterface $accessTokenMapper,
        ModuleOptions $moduleOptions
    ) {
        $this->userMapper = $userMapper;
        $this->thirdPartyMapper = $thirdPartyMapper;
        $this->accessTokenMapper = $accessTokenMapper;
        $this->moduleOptions = $moduleOptions;

        foreach ($moduleOptions->getThirdPartyProviders() as $providerConfig) {
            $provider = ThirdParty\Provider\ProviderFactory::createProvider($providerConfig);
            $this->addProvider($provider);
        }
    }

    /**
     * @return string
     */
    public function getQuerystringIdentifier()
    {
        return 'third_party';
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return bool
     */
    public function validateRequest(RequestInterface $request, ResponseInterface $response)
    {
        $providerName        = $request->request("provider");
        $providerUserId      = $request->request("provider_user_id");
        $providerAccessToken = $request->request("provider_access_token");

        if (! $providerName || ! $providerUserId || ! $providerAccessToken) {
            $response->setError(
                400,
                'invalid_request',
                'One or more missing parameter: "provider", "provider_user_id" and "provider_access_token" are required'
            );

            return false;
        }

        $provider = isset($this->providers[$providerName]) ? $this->providers[$providerName] : null;

        if (! $provider instanceof ProviderInterface) {
            $response->setError(400, 'invalid_request', 'Unknown provider selected');

            return false;
        }

        try {
            $errorMessage = '';
            if (! $provider->validate($providerUserId, $providerAccessToken, $errorMessage)) {
                $response->setError(401, 'invalid_grant', 'Invalid third party credentials: '.$errorMessage);

                return false;
            }
        } catch (ClientException $e) {
            $response->setError($e->getCode(), 'provider_client_error', $e->getMessage());

            return false;
        } catch (Exception $e) {
            $response->setError(500, 'provider_error', $e->getMessage());

            return false;
        }

        $token = $request->request("access_token");
        $accessToken = $token ? $this->accessTokenMapper->findByToken($token) : null;

        if ($accessToken instanceof Entity\AccessToken && $accessToken->isExpired()) {
            $response->setError(401, 'invalid_grant', 'Access token is expired');
            return false;
        }

        $thirdPartyUser = $this->thirdPartyMapper->findByProvider($provider);

        switch (true) {
            // a known user tries to connect with third party credentials owned by another user? issue an error
            case ($accessToken instanceof Entity\AccessToken
                    && $thirdPartyUser instanceof Entity\ThirdParty
                    && $thirdPartyUser->getUser() !== $accessToken->getUser()):
                $response->setError(400, 'invalid_request', 'Another user is already registered with same credentials');
                return false;

            // known third party credentials? update the data and grab the user form it
            case ($thirdPartyUser instanceof Entity\ThirdParty):
                $thirdPartyUser->setData($provider->getUserData());
                $user = $thirdPartyUser->getUser();
                break;

            // valid access token? grab the user form it
            case ($accessToken instanceof Entity\AccessToken):
                $user = $accessToken->getUser();
                break;

            // no third party credentials or access token? it's a new user
            default:
                $userClass = $this->moduleOptions->getUserEntityClassName();
                $user = new $userClass();
        }

        // in case 3 and 4 we need to connect the user with new third party credentials
        if (! $thirdPartyUser instanceof Entity\ThirdParty) {
            $this->connectUserToThirdParty($user, $provider);
        }

        $this->userMapper->save($user);
        $this->user = $user;

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

    /**
     * @return null|string
     */
    public function getScope()
    {
        return $this->user instanceof Entity\ScopesProviderInterface ? $this->user->getScopesString() : null;
    }

    /**
     * @param AccessTokenFactory $accessTokenFactory
     * @param $client_id
     * @param $user_id
     * @param $scope
     * @return mixed
     */
    public function createAccessToken(AccessTokenFactory $accessTokenFactory, $client_id, $user_id, $scope)
    {
        return $accessTokenFactory->createAccessToken($client_id, $user_id, $scope);
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

    /**
     * @param Entity\ThirdPartyAwareUserInterface $user
     * @param ProviderInterface $provider
     */
    protected function connectUserToThirdParty(Entity\ThirdPartyAwareUserInterface $user, ProviderInterface $provider)
    {
        $thirdPartyUser = new Entity\ThirdParty(
            $provider->getUserId(),
            $provider->getIdentifier(),
            $user,
            $provider->getUserData()
        );

        $user->addThirdParty($thirdPartyUser);
    }
}

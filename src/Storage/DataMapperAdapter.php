<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Storage;

use Assert\Assertion;
use DateTime;
use InvalidArgumentException;
use OAuth2\Storage;
use Thorr\OAuth2\DataMapper;
use Thorr\OAuth2\Entity;
use Thorr\OAuth2\GrantType\UserCredentials\PasswordStrategy;
use Thorr\OAuth2\GrantType\UserCredentials\UserCredentialsStrategyInterface;
use Thorr\Persistence\DataMapper\DataMapperInterface;
use Thorr\Persistence\DataMapper\Manager\DataMapperManager;
use Thorr\Persistence\DataMapper\Manager\DataMapperManagerAwareInterface;
use Thorr\Persistence\DataMapper\Manager\DataMapperManagerAwareTrait;
use Zend\Crypt\Password\PasswordInterface;
use Zend\Stdlib\PriorityList;

class DataMapperAdapter implements
    Storage\AuthorizationCodeInterface,
    Storage\AccessTokenInterface,
    Storage\ClientCredentialsInterface,
    Storage\RefreshTokenInterface,
    Storage\ScopeInterface,
    Storage\UserCredentialsInterface,
    DataMapperManagerAwareInterface
{
    use DataMapperManagerAwareTrait;

    /**
     * @var PasswordInterface
     */
    protected $password;

    /**
     * @var string
     */
    protected $userClass = Entity\UserInterface::class;

    /**
     * @var PriorityList
     */
    protected $userCredentialsStrategies;

    /**
     * @param DataMapperManager $dataMapperManager
     * @param PasswordInterface $password
     */
    public function __construct(DataMapperManager $dataMapperManager, PasswordInterface $password, $addDefaultUserCredentialsStrategy = true)
    {
        $this->setDataMapperManager($dataMapperManager);
        $this->setPassword($password);
        $this->userCredentialsStrategies = new PriorityList();
        $this->userCredentialsStrategies->isLIFO(false);

        if ($addDefaultUserCredentialsStrategy) {
            $this->addUserCredentialsStrategy(new PasswordStrategy($password), 'default');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param string $oauthToken
     */
    public function getAccessToken($oauthToken)
    {
        $token = $this->getTokenDataMapper(Entity\AccessToken::class)->findByToken($oauthToken);

        if (! $token instanceof Entity\AccessToken) {
            return;
        }

        return [
            'expires'   => $token->getExpiryUTCTimestamp(),
            'client_id' => $token->getClient()->getUuid(),
            'user_id'   => $token->getUser() ? $token->getUser()->getUuid() : null,
            'scope'     => $token->getScopesString(),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param string      $token
     * @param string      $clientId
     * @param string|null $userId
     */
    public function setAccessToken($token, $clientId, $userId, $expiryTimestamp, $scope = null)
    {
        $authTokenDataMapper = $this->getTokenDataMapper(Entity\AccessToken::class);

        $client = $this->getClientDataMapper()->findByUuid($clientId);

        if (! $accessToken = $authTokenDataMapper->findByToken($token)) {
            $accessToken = new Entity\AccessToken(null, $token, $client);
        } else {
            $accessToken->setClient($client);
        }

        if ($userId) {
            $user = $this->getUserDataMapper()->findByUuid($userId);

            if ($user instanceof $this->userClass) {
                $accessToken->setUser($user);
            }
        }

        $expiryDate = is_int($expiryTimestamp) ? new DateTime('@' . $expiryTimestamp) : null;
        $accessToken->setExpiryDate($expiryDate);

        if ($scope) {
            $scopes = $this->getScopeDataMapper()->findScopes(explode(' ', $scope));
            $accessToken->setScopes($scopes);
        }

        $authTokenDataMapper->save($accessToken);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $code
     */
    public function getAuthorizationCode($code)
    {
        $authorizationCode = $this->getTokenDataMapper(Entity\AuthorizationCode::class)->findByToken($code);

        if (! $authorizationCode instanceof Entity\AuthorizationCode) {
            return;
        }

        return [
            'expires'      => $authorizationCode->getExpiryUTCTimestamp(),
            'client_id'    => $authorizationCode->getClient()->getUuid(),
            'user_id'      => $authorizationCode->getUser()->getUuid(),
            'redirect_uri' => $authorizationCode->getRedirectUri(),
            'scope'        => $authorizationCode->getScopesString(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthorizationCode($code, $clientId, $userId, $redirectUri, $expiryTimestamp, $scope = null)
    {
        $authCodeDataMapper = $this->getTokenDataMapper(Entity\AuthorizationCode::class);

        $client = $this->getClientDataMapper()->findByUuid($clientId);
        $user   = $this->getUserDataMapper()->findByUuid($userId);

        /** @var Entity\AuthorizationCode $authorizationCode */
        $authorizationCode = $authCodeDataMapper->findByToken($code);

        if (! $authorizationCode) {
            $authorizationCode = new Entity\AuthorizationCode(null, $code, $client, $user, null, $redirectUri);
        } else {
            $authorizationCode->setUser($user);
            $authorizationCode->setClient($client);
            $authorizationCode->setRedirectUri($redirectUri);
        }

        $expiryDate = is_int($expiryTimestamp) ? new DateTime('@' . $expiryTimestamp) : null;
        $authorizationCode->setExpiryDate($expiryDate);

        if ($scope) {
            $scopes = $this->getScopeDataMapper()->findScopes(explode(' ', $scope));
            $authorizationCode->setScopes($scopes);
        }

        $authCodeDataMapper->save($authorizationCode);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $token
     */
    public function expireAuthorizationCode($token)
    {
        $authCodeDataMapper = $this->getTokenDataMapper(Entity\AuthorizationCode::class);

        $authorizationCode = $authCodeDataMapper->findByToken($token);
        $authorizationCode->setExpiryDate(new DateTime());

        $authCodeDataMapper->save($authorizationCode);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $clientId
     * @param string $clientSecret
     */
    public function checkClientCredentials($clientId, $clientSecret = null)
    {
        $client = $this->getClientDataMapper()->findByUuid($clientId);

        if (! $client instanceof Entity\Client) {
            return false;
        }

        return $this->password->verify($clientSecret, $client->getSecret());
    }

    /**
     * {@inheritdoc}
     *
     * @param string $clientId
     */
    public function isPublicClient($clientId)
    {
        $client = $this->getClientDataMapper()->findByUuid($clientId);

        if (! $client instanceof Entity\Client) {
            return false;
        }

        return $client->isPublic();
    }

    /**
     * {@inheritdoc}
     *
     * @param string $clientId
     */
    public function getClientDetails($clientId)
    {
        $client = $this->getClientDataMapper()->findByUuid($clientId);

        if (! $client instanceof Entity\Client) {
            return false;
        }

        return [
            'redirect_uri' => $client->getRedirectUri(),
            'client_id'    => $client->getUuid(),
            'grant_types'  => $client->getGrantTypes(),
            'user_id'      => $client->getUser() ? $client->getUser()->getUuid() : null,
            'scope'        => $client->getScopesString(),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param string $clientUuid
     */
    public function getClientScope($clientUuid)
    {
        $client = $this->getClientDataMapper()->findByUuid($clientUuid);

        if (! $client instanceof Entity\Client) {
            throw new InvalidArgumentException('Invalid client uuid provided');
        }

        return $client->getScopesString();
    }

    /**
     * {@inheritdoc}
     *
     * if no grant type is defined for the client, then any type is valid
     *
     * @param string $clientUuid
     * @param string $grantType
     */
    public function checkRestrictedGrantType($clientUuid, $grantType)
    {
        $client = $this->getClientDataMapper()->findByUuid($clientUuid);

        if (! $client instanceof Entity\Client) {
            return false;
        }

        $clientGrantTypes = $client->getGrantTypes();

        if (empty($clientGrantTypes)) {
            return true;
        }

        return in_array($grantType, $clientGrantTypes);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $refreshToken
     */
    public function getRefreshToken($refreshToken)
    {
        $refreshTokenDataMapper = $this->getTokenDataMapper(Entity\RefreshToken::class);

        $token = $refreshTokenDataMapper->findByToken($refreshToken);

        if (! $token instanceof Entity\RefreshToken) {
            return;
        }

        return [
            'refresh_token' => $token->getToken(),
            'client_id'     => $token->getClient()->getUuid(),
            'user_id'       => $token->getUser() ? $token->getUser()->getUuid() : null,
            'expires'       => $token->getExpiryUTCTimestamp(),
            'scope'         => $token->getScopesString(),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param string      $token
     * @param string      $clientId
     * @param string|null $userId
     * @param int|null    $expiryTimestamp
     * @param string      $scope
     */
    public function setRefreshToken($token, $clientId, $userId, $expiryTimestamp, $scope = null)
    {
        $refreshTokenDataMapper = $this->getTokenDataMapper(Entity\RefreshToken::class);

        $client = $this->getClientDataMapper()->findByUuid($clientId);

        if (! $refreshToken = $refreshTokenDataMapper->findByToken($token)) {
            $refreshToken = new Entity\RefreshToken(null, $token, $client);
        } else {
            $refreshToken->setClient($client);
        }

        if ($userId) {
            $user = $this->getUserDataMapper()->findByUuid($userId);
            if ($user instanceof $this->userClass) {
                $refreshToken->setUser($user);
            }
        }

        $expiryDate = is_int($expiryTimestamp) ? new DateTime('@' . $expiryTimestamp) : null;
        $refreshToken->setExpiryDate($expiryDate);

        if ($scope) {
            $scopes = $this->getScopeDataMapper()->findScopes(explode(' ', $scope));
            $refreshToken->setScopes($scopes);
        }

        $refreshTokenDataMapper->save($refreshToken);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $token
     */
    public function unsetRefreshToken($token)
    {
        $refreshTokenDataMapper = $this->getTokenDataMapper(Entity\RefreshToken::class);

        $refreshToken = $refreshTokenDataMapper->findByToken($token);

        if (! $refreshToken instanceof Entity\RefreshToken) {
            throw new InvalidArgumentException('Invalid token provided');
        }

        $refreshTokenDataMapper->remove($refreshToken);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $scopesString
     */
    public function scopeExists($scopesString)
    {
        $scopes      = explode(' ', $scopesString);
        $foundScopes = $this->getScopeDataMapper()->findScopes($scopes);
        $inputScopes = $scopes;

        $matches = array_filter($foundScopes, function (Entity\Scope $scope) use (&$inputScopes) {
            $result = in_array($scope, $inputScopes);
            if ($result) {
                $matchKey = array_search($scope, $inputScopes);
                unset($inputScopes[$matchKey]);
            }

            return $result;
        });

        return count($matches) === count($scopes);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultScope($clientId = null)
    {
        $scopes = $this->getScopeDataMapper()->findDefaultScopes();

        if (! count($scopes)) {
            return;
        }

        $scopeNames = array_map(
            function (Entity\Scope $scope) {
                return $scope->getName();
            },
            $scopes
        );

        return implode(' ', $scopeNames);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $credential
     * @param string $password
     */
    public function checkUserCredentials($credential, $password)
    {
        $user = $this->getUserDataMapper()->findByCredential($credential);

        $userClass = $this->userClass;

        if (! $user instanceof $userClass) {
            return false;
        }

        foreach ($this->userCredentialsStrategies as $strategy) {
            $result = $strategy instanceof UserCredentialsStrategyInterface
                ? $strategy->isValid($user, $password)
                : call_user_func($strategy, $user, $password);

            if (! $result) {
                break;
            }
        }

        return isset($result) ? $result : false;
    }

    /**
     * {@inheritdoc}
     *
     * @param string|null $credential
     */
    public function getUserDetails($credential)
    {
        $user = $this->getUserDataMapper()->findByCredential($credential);

        if (! $user instanceof $this->userClass) {
            return false;
        }

        return [
            'user_id' => (string) $user->getUuid(),
            'scope'   => $user instanceof Entity\ScopesProviderInterface ? $user->getScopesString() : null,
        ];
    }

    /**
     * @return PasswordInterface
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param PasswordInterface $password
     */
    public function setPassword(PasswordInterface $password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getUserClass()
    {
        return $this->userClass;
    }

    /**
     * @param string $userClass
     */
    public function setUserClass($userClass)
    {
        if (! class_exists($userClass) || ! is_a($userClass, Entity\UserInterface::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid user class: must implement %s',
                Entity\UserInterface::class
            ));
        }

        $this->userClass = $userClass;
    }

    /**
     * @return DataMapper\ScopeMapperInterface
     */
    protected function getScopeDataMapper()
    {
        return $this->getDataMapperManager()->getDataMapperForEntity(Entity\Scope::class);
    }

    /**
     * @param string $tokenClass
     *
     * @return DataMapper\TokenMapperInterface
     */
    protected function getTokenDataMapper($tokenClass)
    {
        return $this->getDataMapperManager()->getDataMapperForEntity($tokenClass);
    }

    /**
     * @return DataMapperInterface
     */
    protected function getClientDataMapper()
    {
        return $this->getDataMapperManager()->getDataMapperForEntity(Entity\Client::class);
    }

    /**
     * @return DataMapper\UserMapperInterface
     */
    protected function getUserDataMapper()
    {
        $userDataMapper = $this->getDataMapperManager()->getDataMapperForEntity($this->userClass);

        Assertion::isInstanceOf($userDataMapper, DataMapper\UserMapperInterface::class);

        return $userDataMapper;
    }

    /**
     * @param int $flag extraction flag @see PriorityList
     *
     * @return array
     */
    public function getUserCredentialsStrategies($flag = PriorityList::EXTR_DATA)
    {
        return $this->userCredentialsStrategies->toArray($flag);
    }

    /**
     * @param callable|UserCredentialsStrategyInterface $strategy
     * @param string                                    $name
     */
    public function addUserCredentialsStrategy($strategy, $name, $priority = 0)
    {
        if (! is_callable($strategy)
            && ! $strategy instanceof UserCredentialsStrategyInterface) {
            throw new InvalidArgumentException(sprintf(
                "User credential strategy must be a callable or implement '%s'",
                UserCredentialsStrategyInterface::class
            ));
        }

        $this->userCredentialsStrategies->insert($name, $strategy, $priority);
    }

    /**
     * @param string $name
     */
    public function removeUserCredentialsStrategy($name)
    {
        $this->userCredentialsStrategies->remove($name);
    }
}

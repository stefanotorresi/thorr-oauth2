<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Storage;

use DateTime;
use InvalidArgumentException;
use OAuth2\Storage;
use Thorr\OAuth2\DataMapper;
use Thorr\OAuth2\Entity;
use Thorr\OAuth2\Entity\UserInterface;
use Thorr\Persistence\DataMapper\DataMapperInterface;
use Thorr\Persistence\DataMapper\Manager\DataMapperManager;
use Thorr\Persistence\DataMapper\Manager\DataMapperManagerAwareInterface;
use Thorr\Persistence\DataMapper\Manager\DataMapperManagerAwareTrait;
use Zend\Crypt\Password\PasswordInterface;

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
     * @param PasswordInterface $password
     * @param DataMapperManager $dataMapperManager
     */
    public function __construct(PasswordInterface $password, DataMapperManager $dataMapperManager)
    {
        $this->setPassword($password);
        $this->setDataMapperManager($dataMapperManager);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($oauthToken)
    {
        $authTokenDataMapper = $this->getTokenDataMapper(Entity\AccessToken::class);

        $token = $authTokenDataMapper->findByToken($oauthToken);

        if (! $token instanceof Entity\AccessToken) {
            return;
        }

        return [
            'expires'   => $token->getExpiryUTCTimestamp(),
            'client_id' => $token->getClient()->getId(),
            'user_id'   => $token->getUser() ? $token->getUser()->getId() : null,
            'scope'     => $token->getScopesString(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setAccessToken($oauthToken, $clientId, $userId, $expires, $scope = null)
    {
        $authTokenDataMapper = $this->getTokenDataMapper(Entity\AccessToken::class);

        if (! $token = $authTokenDataMapper->findByToken($oauthToken)) {
            $token = new Entity\AccessToken();
        }

        $clientDataMapper = $this->getDataMapperManager()->getDataMapperForEntity(Entity\Client::class);

        $client = $clientDataMapper->findById($clientId);

        $userDataMapper = $this->getDataMapperManager()->getDataMapperForEntity(Entity\User::class);

        if ($userId) {
            $user = $userDataMapper->findById($userId);

            if ($user instanceof Entity\User) {
                $token->setUser($user);
            }
        }

        $token->setToken($oauthToken);
        $token->setClient($client);
        $token->setExpiryDate(new DateTime('@' . $expires));

        if ($scope) {
            $scopeDataMapper = $this->getScopeDataMapper();
            $scopes = $scopeDataMapper->findScopes(explode(' ', $scope));
            $token->setScopes($scopes);
        }

        $authTokenDataMapper->save($token);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationCode($code)
    {
        $authCodeDataMapper = $this->getTokenDataMapper(Entity\AuthorizationCode::class);

        $authorizationCode = $authCodeDataMapper->findByToken($code);

        if (! $authorizationCode instanceof Entity\AuthorizationCode || $authorizationCode->isExpired()) {
            return;
        }

        return [
            'expires'      => $authorizationCode->getExpiryUTCTimestamp(),
            'client_id'    => $authorizationCode->getClient()->getId(),
            'user_id'      => $authorizationCode->getUser()->getId(),
            'redirect_uri' => $authorizationCode->getRedirectUri(),
            'scopes'       => $authorizationCode->getScopesString()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthorizationCode($code, $clientId, $userId, $redirectUri, $expires, $scope = null)
    {
        $authCodeDataMapper = $this->getTokenDataMapper(Entity\AuthorizationCode::class);

        if (! $authorizationCode = $authCodeDataMapper->findByToken($code)) {
            $authorizationCode = new Entity\AuthorizationCode();
        }

        $clientDataMapper = $this->getDataMapperManager()->getDataMapperForEntity(Entity\Client::class);
        $client = $clientDataMapper->findById($clientId);

        if (! $client instanceof Entity\Client) {
            throw new InvalidArgumentException('Invalid clientId provided');
        }

        $userDataMapper = $this->getDataMapperManager()->getDataMapperForEntity(Entity\User::class);
        $user = $userDataMapper->findById($userId);

        if (! $user instanceof Entity\User) {
            throw new InvalidArgumentException('Invalid userId provided');
        }

        $authorizationCode->setToken($code);
        $authorizationCode->setClient($client);
        $authorizationCode->setUser($user);
        $authorizationCode->setExpiryDate(new DateTime('@' . $expires));
        $authorizationCode->setRedirectUri($redirectUri);

        if ($scope) {
            $scopeDataMapper = $this->getScopeDataMapper();
            $scopes = $scopeDataMapper->findScopes(explode(' ', $scope));
            $authorizationCode->setScopes($scopes);
        }

        $authCodeDataMapper->save($authorizationCode);
    }

    /**
     * {@inheritdoc}
     */
    public function expireAuthorizationCode($code)
    {
        $authCodeDataMapper = $this->getTokenDataMapper(Entity\AuthorizationCode::class);

        $authorizationCode = $authCodeDataMapper->findByToken($code);

        $authorizationCode->setExpiryDate(new DateTime());

        $authCodeDataMapper->save($authorizationCode);
    }

    /**
     * {@inheritdoc}
     */
    public function checkClientCredentials($clientId, $clientSecret = null)
    {
        $clientDataMapper = $this->getDataMapperManager()->getDataMapperForEntity(Entity\Client::class);

        $client = $clientDataMapper->findById($clientId);

        if (! $client instanceof Entity\Client) {
            return false;
        }

        return $this->password->verify($clientSecret, $client->getSecret());
    }

    /**
     * {@inheritdoc}
     */
    public function isPublicClient($clientId)
    {
        $clientDataMapper = $this->getDataMapperManager()->getDataMapperForEntity(Entity\Client::class);

        $client = $clientDataMapper->findById($clientId);

        if (! $client instanceof Entity\Client) {
            return false;
        }

        return $client->isPublic();
    }

    /**
     * {@inheritdoc}
     */
    public function getClientDetails($clientId)
    {
        $clientDataMapper = $this->getDataMapperManager()->getDataMapperForEntity(Entity\Client::class);

        $client = $clientDataMapper->findById($clientId);

        if (! $client instanceof Entity\Client) {
            return false;
        }

        return [
            'redirect_uri' => $client->getRedirectUri(),
            'client_id'    => $client->getId(),
            'grant_types'  => $client->getGrantTypes(),
            'user_id'      => $client->getUser() ? $client->getUser()->getId() : null,
            'scope'        => $client->getScopesString(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getClientScope($clientId)
    {
        $clientDataMapper = $this->getDataMapperManager()->getDataMapperForEntity(Entity\Client::class);

        $client = $clientDataMapper->findById($clientId);

        if (! $client instanceof Entity\Client) {
            throw new InvalidArgumentException('Invalid clientId provided');
        }

        return $client->getScopesString();
    }

    /**
     * {@inheritdoc}
     */
    public function checkRestrictedGrantType($clientId, $grantType)
    {
        $clientDataMapper = $this->getDataMapperManager()->getDataMapperForEntity(Entity\Client::class);

        $client = $clientDataMapper->findById($clientId);

        if (! $client instanceof Entity\Client) {
            return false;
        }

        $grantTypes = $client->getGrantTypes();

        if (empty($grantTypes)) {
            return true;
        }

        return in_array($grantType, $grantTypes);
    }

    /**
     * {@inheritdoc}
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
            'client_id'     => $token->getClient()->getId(),
            'user_id'       => $token->getUser()->getId(),
            'expires'       => $token->getExpiryUTCTimestamp(),
            'scope'         => $token->getScopesString(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setRefreshToken($token, $clientId, $userId, $expires, $scope = null)
    {
        $refreshTokenDataMapper = $this->getTokenDataMapper(Entity\RefreshToken::class);

        if (! $refreshToken = $refreshTokenDataMapper->findByToken($token)) {
            $refreshToken = new Entity\RefreshToken();
        }

        $clientDataMapper = $this->getDataMapperManager()->getDataMapperForEntity(Entity\Client::class);

        $client = $clientDataMapper->findById($clientId);

        $userDataMapper = $this->getDataMapperManager()->getDataMapperForEntity(Entity\User::class);

        if ($userId) {
            $user = $userDataMapper->findById($userId);
            if ($user instanceof Entity\User) {
                $refreshToken->setUser($user);
            }
        }

        $refreshToken->setToken($token);
        $refreshToken->setClient($client);
        $refreshToken->setExpiryDate(new DateTime('@' . $expires));

        if ($scope) {
            $scopeDataMapper = $this->getScopeDataMapper();
            $scopes = $scopeDataMapper->findScopes(explode(' ', $scope));
            $refreshToken->setScopes($scopes);
        }

        $refreshTokenDataMapper->save($refreshToken);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetRefreshToken($token)
    {
        $refreshTokenDataMapper = $this->getTokenDataMapper(Entity\RefreshToken::class);

        $refreshToken = $refreshTokenDataMapper->findByToken($token);

        $refreshTokenDataMapper->remove($refreshToken);
    }

    /**
     * {@inheritdoc}
     */
    public function scopeExists($scopes)
    {
        $scopes = explode(' ', $scopes);

        $scopeDataMapper = $this->getScopeDataMapper();

        $result = $scopeDataMapper->findScopes($scopes);

        return count($scopes) === count($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultScope($clientId = null)
    {
        /** @var DataMapper\ScopeMapperInterface $scopeDataMapper */


        $scopes = $scopeDataMapper->findDefaultScopes();

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
     */
    public function checkUserCredentials($userId, $password)
    {
        $userDataMapper = $this->getDataMapperManager()->getDataMapperForEntity(Entity\User::class);

        $user = $userDataMapper->findById($userId);

        if (! $user instanceof UserInterface) {
            return false;
        }

        return $this->password->verify($password, $user->getPassword());
    }

    /**
     * {@inheritdoc}
     */
    public function getUserDetails($userId)
    {
        $userDataMapper = $this->getDataMapperManager()->getDataMapperForEntity(Entity\User::class);

        $user = $userDataMapper->findById($userId);

        if (! $user instanceof UserInterface) {
            return false;
        }

        return [
            'user_id' => $user->getId(),
            'scope' => $user instanceof Entity\ScopesProviderInterface ? $user->getScopesString() : null
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
     * @return DataMapper\ScopeMapperInterface
     */
    protected function getScopeDataMapper()
    {
        $scopeDataMapper = $this->getDataMapperManager()->getDataMapperForEntity(Entity\Scope::class);

        $this->guardDataMapperType($scopeDataMapper, DataMapper\ScopeMapperInterface::class, Entity\Scope::class);

        return $scopeDataMapper;
    }

    /**
     * @param  string $tokenClass
     * @return DataMapper\TokenMapperInterface
     */
    protected function getTokenDataMapper($tokenClass)
    {
        $tokenDataMapper = $this->getDataMapperManager()->getDataMapperForEntity($tokenClass);

        $this->guardDataMapperType($tokenDataMapper, DataMapper\TokenMapperInterface::class, $tokenClass);

        return $tokenDataMapper;
    }

    /**
     * @param DataMapperInterface $dataMapper
     * @param string              $dataMapperClass
     * @param string              $entityClass
     */
    protected function guardDataMapperType(DataMapperInterface $dataMapper, $dataMapperClass, $entityClass)
    {
        if (! is_a($dataMapper, $dataMapperClass)) {
            throw new \RuntimeException(sprintf(
                "Invalid data mapper type for entity '%s'. Expected '%s', got '%s'",
                $entityClass,
                DataMapper\ScopeMapperInterface::class,
                get_class($dataMapper)
            ));
        }
    }
}

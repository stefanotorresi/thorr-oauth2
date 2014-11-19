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
     * @var string
     */
    protected $userClass = Entity\UserInterface::class;

    /**
     * @param DataMapperManager $dataMapperManager
     * @param PasswordInterface $password
     */
    public function __construct(DataMapperManager $dataMapperManager, PasswordInterface $password)
    {
        $this->setDataMapperManager($dataMapperManager);
        $this->setPassword($password);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($oauthToken)
    {
        $token = $this->getTokenDataMapper(Entity\AccessToken::class)->findByToken($oauthToken);

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
    public function setAccessToken($token, $clientId, $userId, $expiryTimestamp, $scope = null)
    {
        $authTokenDataMapper = $this->getTokenDataMapper(Entity\AccessToken::class);

        $client = $this->getClientDataMapper()->findById($clientId);

        if (! $accessToken = $authTokenDataMapper->findByToken($token)) {
            $accessToken = new Entity\AccessToken($token, $client);
        } else {
            $accessToken->setClient($client);
        }

        if ($userId) {
            $user = $this->getUserDataMapper()->findById($userId);

            if ($user instanceof Entity\User) {
                $accessToken->setUser($user);
            }
        }

        $expiryDate = is_int($expiryTimestamp) ? new DateTime('@'.$expiryTimestamp) : null;
        $accessToken->setExpiryDate($expiryDate);

        if ($scope) {
            $scopes = $this->getScopeDataMapper()->findScopes(explode(' ', $scope));
            $accessToken->setScopes($scopes);
        }

        $authTokenDataMapper->save($accessToken);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationCode($code)
    {
        $authorizationCode = $this->getTokenDataMapper(Entity\AuthorizationCode::class)->findByToken($code);

        if (! $authorizationCode instanceof Entity\AuthorizationCode) {
            return;
        }

        return [
            'expires'      => $authorizationCode->getExpiryUTCTimestamp(),
            'client_id'    => $authorizationCode->getClient()->getId(),
            'user_id'      => $authorizationCode->getUser()->getId(),
            'redirect_uri' => $authorizationCode->getRedirectUri(),
            'scope'        => $authorizationCode->getScopesString()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthorizationCode($code, $clientId, $userId, $redirectUri, $expiryTimestamp, $scope = null)
    {
        $authCodeDataMapper = $this->getTokenDataMapper(Entity\AuthorizationCode::class);

        $client = $this->getClientDataMapper()->findById($clientId);

        if (! $authorizationCode = $authCodeDataMapper->findByToken($code)) {
            $authorizationCode = new Entity\AuthorizationCode($code, $client);
        } else {
            $authorizationCode->setClient($client);
        }

        $user = $this->getUserDataMapper()->findById($userId);
        $authorizationCode->setUser($user);

        $expiryDate = is_int($expiryTimestamp) ? new DateTime('@'.$expiryTimestamp) : null;
        $authorizationCode->setExpiryDate($expiryDate);
        $authorizationCode->setRedirectUri($redirectUri);

        if ($scope) {
            $scopes = $this->getScopeDataMapper()->findScopes(explode(' ', $scope));
            $authorizationCode->setScopes($scopes);
        }

        $authCodeDataMapper->save($authorizationCode);
    }

    /**
     * {@inheritdoc}
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
     */
    public function checkClientCredentials($clientId, $clientSecret = null)
    {
        $client = $this->getClientDataMapper()->findById($clientId);

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
        $client = $this->getClientDataMapper()->findById($clientId);

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
        $client = $this->getClientDataMapper()->findById($clientId);

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
        $client = $this->getClientDataMapper()->findById($clientId);

        if (! $client instanceof Entity\Client) {
            throw new InvalidArgumentException('Invalid clientId provided');
        }

        return $client->getScopesString();
    }

    /**
     * {@inheritdoc}
     *
     * if no grant type is defined for the client, then any type is valid
     */
    public function checkRestrictedGrantType($clientId, $grantType)
    {
        $client = $this->getClientDataMapper()->findById($clientId);

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
            'user_id'       => $token->getUser() ? $token->getUser()->getId() : null,
            'expires'       => $token->getExpiryUTCTimestamp(),
            'scope'         => $token->getScopesString(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setRefreshToken($token, $clientId, $userId, $expiryTimestamp, $scope = null)
    {
        $refreshTokenDataMapper = $this->getTokenDataMapper(Entity\RefreshToken::class);

        $client = $this->getClientDataMapper()->findById($clientId);

        if (! $refreshToken = $refreshTokenDataMapper->findByToken($token)) {
            $refreshToken = new Entity\RefreshToken($token, $client);
        } else {
            $refreshToken->setClient($client);
        }

        if ($userId) {
            $user = $this->getUserDataMapper()->findById($userId);
            if ($user instanceof Entity\User) {
                $refreshToken->setUser($user);
            }
        }

        $expiryDate = is_int($expiryTimestamp) ? new DateTime('@'.$expiryTimestamp) : null;
        $refreshToken->setExpiryDate($expiryDate);

        if ($scope) {
            $scopes = $this->getScopeDataMapper()->findScopes(explode(' ', $scope));
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

        if (! $refreshToken instanceof Entity\RefreshToken) {
            throw new InvalidArgumentException('Invalid token provided');
        }

        $refreshTokenDataMapper->remove($refreshToken);
    }

    /**
     * {@inheritdoc}
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
     */
    public function checkUserCredentials($credential, $password)
    {
        $user = $this->getUserDataMapper()->findByCredential($credential);

        if (! $user instanceof UserInterface) {
            return false;
        }

        return $this->password->verify($password, $user->getPassword());
    }

    /**
     * {@inheritdoc}
     */
    public function getUserDetails($credential)
    {
        $user = $this->getUserDataMapper()->findByCredential($credential);

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
     * @param  string                          $tokenClass
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
        return $this->getDataMapperManager()->getDataMapperForEntity($this->userClass);
    }
}

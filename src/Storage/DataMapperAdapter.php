<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Storage;

use OAuth2\Storage;
use Thorr\OAuth\Entity;
use Thorr\OAuth\Repository\RepositoryManagerWrapperTrait;
use Thorr\Persistence\Repository\Manager\RepositoryManager;
use Thorr\Persistence\Repository\Manager\RepositoryManagerAwareInterface;
use Thorr\Persistence\Repository\Manager\RepositoryManagerAwareTrait;
use Zend\Crypt\Password\Bcrypt;

class DataMapperAdapter implements
    ThirdPartyProviderInterface,
    Storage\AuthorizationCodeInterface,
    Storage\AccessTokenInterface,
    Storage\ClientCredentialsInterface,
    Storage\RefreshTokenInterface,
    Storage\ScopeInterface,
    Storage\UserCredentialsInterface,
    RepositoryManagerAwareInterface
{
    use RepositoryManagerWrapperTrait;

    /**
     * @var Bcrypt
     */
    protected $bcrypt;

    /**
     * @param Bcrypt            $bcrypt
     * @param RepositoryManager $repositoryManager
     */
    public function __construct(Bcrypt $bcrypt, RepositoryManager $repositoryManager)
    {
        $this->setBcrypt($bcrypt);
        $this->setRepositoryManager($repositoryManager);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($oauthToken)
    {
        $token = $this->getAccessTokenRepository()->find($oauthToken);

        if (! $token) {
            return;
        }

        return [
            'expires'   => $token->getExpirationDate() ? $token->getExpirationDate()->getTimestamp() : 0,
            'client_id' => $token->getClient()->getId(),
            'user_id'   => $token->getUser()->getId(),
            'scope'     => $token->getScopesString(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setAccessToken($oauthToken, $clientId, $userId, $expires, $scope = null)
    {
        $client = $this->getClientRepository()->find($clientId);

        if (! $client) {
            throw new \InvalidArgumentException('Invalid clientId provided');
        }

        $user = $this->getUserRepository()->find($userId);

        if (! $user) {
            throw new \InvalidArgumentException('Invalid userId provided');
        }

        $token = (new Entity\AccessToken())
            ->setToken($oauthToken)
            ->setClient($client)
            ->setUser($user)
            ->setExpirationDate(new \DateTime('@' . $expires))
        ;

        if ($scope) {
            $token->setScopes(explode(' ', $scope));
        }

        $this->getAccessTokenRepository()->save($token);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationCode($code)
    {
        $authorizationCode = $this->getAuthorizationCodeRepository()->find($code);

        if (! $authorizationCode || $authorizationCode->isExpired()) {
            return;
        }

        return [
            'expires'      => $authorizationCode->getExpirationDate()
                                ? $authorizationCode->getExpirationDate()->getTimestamp() : 0,
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
        $client = $this->getClientRepository()->find($clientId);

        if (! $client) {
            throw new \InvalidArgumentException('Invalid clientId provided');
        }

        $user = $this->getUserRepository()->find($userId);

        if (! $user) {
            throw new \InvalidArgumentException('Invalid userId provided');
        }

        $authorizationCode = new Entity\AuthorizationCode();
        $authorizationCode
            ->setToken($code)
            ->setClient($client)
            ->setUser($user)
            ->setExpirationDate(new \DateTime('@' . $expires))
        ;
        $authorizationCode->setRedirectUri($redirectUri);

        if ($scope) {
            $authorizationCode->setScopes(explode(' ', $scope));
        }

        $this->getAuthorizationCodeRepository()->save($authorizationCode);
    }

    /**
     * {@inheritdoc}
     */
    public function expireAuthorizationCode($code)
    {
        $authorizationCode = $this->getAuthorizationCodeRepository()->find($code);

        $this->getAuthorizationCodeRepository()->remove($authorizationCode);
    }

    /**
     * {@inheritdoc}
     */
    public function checkClientCredentials($clientId, $clientSecret = null)
    {
        $client = $this->getClientRepository()->find($clientId);

        if (! $client) {
            return false;
        }

        return $this->bcrypt->verify($clientSecret, $client->getSecret());
    }

    /**
     * {@inheritdoc}
     */
    public function isPublicClient($clientId)
    {
        $client = $this->getClientRepository()->find($clientId);

        if (! $client) {
            return false;
        }

        return $client->isPublic();
    }

    /**
     * {@inheritdoc}
     */
    public function getClientDetails($clientId)
    {
        $client = $this->getClientRepository()->find($clientId);

        if (! $client) {
            return false;
        }

        return [
            'redirect_uri' => $client->getRedirectUri(),
            'client_id'    => $client->getId(),
            'grant_types'  => $client->getGrantTypes(),
            'user_id'      => $client->getUser()->getId(),
            'scope'        => $client->getScopesString(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getClientScope($clientId)
    {
        $client = $this->getClientRepository()->find($clientId);

        if (! $client) {
            throw new \InvalidArgumentException('Invalid clientId provided');
        }

        return $client->getScopesString();
    }

    /**
     * {@inheritdoc}
     */
    public function checkRestrictedGrantType($clientId, $grantType)
    {
        $client = $this->getClientRepository()->find($clientId);

        if (! $client) {
            return false;
        }

        return in_array($grantType, $client->getGrantTypes());
    }

    /**
     * {@inheritdoc}
     */
    public function getRefreshToken($refreshToken)
    {
        $token = $this->getRefreshTokenRepository()->find($refreshToken);

        if (! $token) {
            return;
        }

        return [
            'refresh_token' => $token->getToken(),
            'client_id'     => $token->getClient()->getId(),
            'user_id'       => $token->getUser()->getId(),
            'expires'       => $token->getExpirationDate() ? $token->getExpirationDate()->getTimestamp() : 0,
            'scope'         => $token->getScopesString(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setRefreshToken($refreshToken, $clientId, $userId, $expires, $scope = null)
    {
        $client = $this->getClientRepository()->find($clientId);

        if (! $client) {
            throw new \InvalidArgumentException('Invalid clientId provided');
        }

        $user = $this->getUserRepository()->find($userId);

        if (! $user) {
            throw new \InvalidArgumentException('Invalid userId provided');
        }

        $token = (new Entity\RefreshToken())
            ->setToken($refreshToken)
            ->setClient($client)
            ->setUser($user)
            ->setExpirationDate(new \DateTime('@' . $expires))
        ;

        if ($scope) {
            $token->setScopes(explode(' ', $scope));
        }

        $this->getRefreshTokenRepository()->save($token);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetRefreshToken($token)
    {
        $refreshToken = $this->getRefreshTokenRepository()->find($token);

        $this->getRefreshTokenRepository()->remove($refreshToken);
    }

    /**
     * {@inheritdoc}
     */
    public function scopeExists($scopes)
    {
        $scopes = explode(' ', $scopes);

        $result = $this->getScopeRepository()->findScopes($scopes);

        return count($scopes) === count($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultScope($clientId = null)
    {
        $scopes = $this->getScopeRepository()->findDefaultScopes();

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
        $user = $this->getUserRepository()->find($userId);

        if (! $user) {
            return false;
        }

        return $this->bcrypt->verify($password, $user->getPassword());
    }

    /**
     * {@inheritdoc}
     */
    public function getUserDetails($userId)
    {
        $user = $this->getUserRepository()->find($userId);

        if (! $user) {
            return false;
        }

        return [
            'user_id' => $user->getId(),
        ];
    }

    public function createUser()
    {
        // TODO: Implement createUser() method.
    }

    /**
     * @return Bcrypt
     */
    public function getBcrypt()
    {
        return $this->bcrypt;
    }

    /**
     * @param Bcrypt $bcrypt
     */
    public function setBcrypt(Bcrypt $bcrypt)
    {
        $this->bcrypt = $bcrypt;
    }
}

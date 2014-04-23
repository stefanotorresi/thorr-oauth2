<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Storage;

use OAuth2\Storage;
use Thorr\OAuth\Repository;
use Zend\Crypt\Password\Bcrypt;

class DataMapperAdapter implements
    Storage\AuthorizationCodeInterface,
    Storage\AccessTokenInterface,
    Storage\ClientCredentialsInterface,
    Storage\RefreshTokenInterface,
    Storage\ScopeInterface,
    Storage\UserCredentialsInterface,
    Repository\UserRepositoryAwareInterface
{
    use Repository\UserRepositoryAwareTrait;

    /**
     * @var Bcrypt
     */
    protected $bcrypt;

    /**
     * @param Bcrypt                                    $bcrypt
     * @param Repository\AccessTokenRepositoryInterface $accessTokenRepository
     * @param Repository\UserRepositoryInterface        $userRepository
     */
    public function __construct(
        Bcrypt $bcrypt,
        Repository\AccessTokenRepositoryInterface $accessTokenRepository,
        Repository\UserRepositoryInterface $userRepository
    ) {
        $this->setUserRepository($userRepository);
        $this->setBcrypt($bcrypt);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($oauthToken)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function setAccessToken($oauthToken, $clientId, $userId, $expires, $scope = null)
    {
        // TODO: Implement setAccessToken() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationCode($code)
    {
        // TODO: Implement getAuthorizationCode() method.
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthorizationCode($code, $clientId, $userId, $redirectUri, $expires, $scope = null)
    {
        // TODO: Implement setAuthorizationCode() method.
    }

    /**
     * {@inheritdoc}
     */
    public function expireAuthorizationCode($code)
    {
        // TODO: Implement expireAuthorizationCode() method.
    }

    /**
     * {@inheritdoc}
     */
    public function checkClientCredentials($clientId, $clientSecret = null)
    {
        // TODO: Implement checkClientCredentials() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isPublicClient($clientId)
    {
        // TODO: Implement isPublicClient() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getClientDetails($clientId)
    {
        // TODO: Implement getClientDetails() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getClientScope($clientId)
    {
        // TODO: Implement getClientScope() method.
    }

    /**
     * {@inheritdoc}
     */
    public function checkRestrictedGrantType($clientId, $grantType)
    {
        // TODO: Implement checkRestrictedGrantType() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getRefreshToken($refreshToken)
    {
        // TODO: Implement getRefreshToken() method.
    }

    /**
     * {@inheritdoc}
     */
    public function setRefreshToken($refreshToken, $clientId, $userId, $expires, $scope = null)
    {
        // TODO: Implement setRefreshToken() method.
    }

    /**
     * {@inheritdoc}
     */
    public function unsetRefreshToken($refreshToken)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function scopeExists($scopes)
    {
        // TODO: Implement unsetRefreshToken() method.
        $scopes = explode(' ', $scopes);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultScope($clientId = null)
    {
        // TODO: Implement unsetRefreshToken() method.
    }

    /**
     * {@inheritdoc}
     */
    public function checkUserCredentials($userId, $password)
    {
        $user = $this->userRepository->find($userId);

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
        $user = $this->userRepository->find($userId);

        if (! $user) {
            return false;
        }

        return [
            'user_id' => $user->getId(),
        ];
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

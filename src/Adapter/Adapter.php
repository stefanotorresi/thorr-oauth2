<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Adapter;

use OAuth2\Storage;
use Thorr\OAuth\Repository\UserRepositoryAwareInterface;
use Thorr\OAuth\Repository\UserRepositoryAwareTrait;
use Thorr\OAuth\Repository\UserRepositoryInterface;
use ZF\OAuth2\Adapter\BcryptTrait;

class Adapter implements
    Storage\AuthorizationCodeInterface,
    Storage\ClientCredentialsInterface,
    Storage\UserCredentialsInterface,
    Storage\RefreshTokenInterface,
    UserRepositoryAwareInterface
{
    use BcryptTrait;
    use UserRepositoryAwareTrait;

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->setUserRepository($userRepository);
    }

    /**
     * {@inheritdoc}
     */
    public function checkUserCredentials($username, $password)
    {
        $user = $this->userRepository->findOneByUsername($username);

        if (! $user) {
            return false;
        }

        return $this->verifyHash($password, $user->getPassword());
    }

    /**
     * {@inheritdoc}
     */
    public function getUserDetails($username)
    {
        $user = $this->userRepository->findOneByUsername($username);

        if (! $user) {
            return false;
        }

        return [
            'user_id' => $user->getUsername(),
        ];
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
    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null)
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
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        // TODO: Implement checkClientCredentials() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isPublicClient($client_id)
    {
        // TODO: Implement isPublicClient() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getClientDetails($client_id)
    {
        // TODO: Implement getClientDetails() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getClientScope($client_id)
    {
        // TODO: Implement getClientScope() method.
    }

    /**
     * {@inheritdoc}
     */
    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        // TODO: Implement checkRestrictedGrantType() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getRefreshToken($refresh_token)
    {
        // TODO: Implement getRefreshToken() method.
    }

    /**
     * {@inheritdoc}
     */
    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null)
    {
        // TODO: Implement setRefreshToken() method.
    }

    /**
     * {@inheritdoc}
     */
    public function unsetRefreshToken($refresh_token)
    {
        // TODO: Implement unsetRefreshToken() method.
    }
}

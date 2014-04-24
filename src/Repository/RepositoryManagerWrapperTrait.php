<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Repository;

use Thorr\Persistence\Repository\Manager\RepositoryManagerAwareTrait;

trait RepositoryManagerWrapperTrait
{
    use RepositoryManagerAwareTrait;

    /**
     * @return AccessTokenRepositoryInterface
     */
    public function getAccessTokenRepository()
    {
        return $this->getRepositoryManager()->get('Thorr\OAuth\Repository\AccessTokenRepository');
    }

    /**
     * @return AuthorizationCodeRepositoryInterface
     */
    public function getAuthorizationCodeRepository()
    {
        return $this->getRepositoryManager()->get('Thorr\OAuth\Repository\AuthorizationCodeRepository');
    }

    /**
     * @return ClientRepositoryInterface
     */
    public function getClientRepository()
    {
        return $this->getRepositoryManager()->get('Thorr\OAuth\Repository\ClientRepository');
    }

    /**
     * @return RefreshTokenRepositoryInterface
     */
    public function getRefreshTokenRepository()
    {
        return $this->getRepositoryManager()->get('Thorr\OAuth\Repository\RefreshTokenRepository');
    }

    /**
     * @return ScopeRepositoryInterface
     */
    public function getScopeRepository()
    {
        return $this->getRepositoryManager()->get('Thorr\OAuth\Repository\ScopeRepository');
    }

    /**
     * @return UserRepositoryInterface
     */
    public function getUserRepository()
    {
        return $this->getRepositoryManager()->get('Thorr\OAuth\Repository\UserRepository');
    }
}

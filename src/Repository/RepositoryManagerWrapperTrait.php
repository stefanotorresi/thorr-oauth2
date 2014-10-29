<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Repository;

use Thorr\Persistence\Repository\Manager\RepositoryManagerAwareTrait;

trait RepositoryManagerWrapperTrait
{
    use RepositoryManagerAwareTrait;

    /**
     * @return AccessTokenRepositoryInterface
     */
    public function getAccessTokenRepository()
    {
        return $this->getRepositoryManager()->get('Thorr\OAuth2\Repository\AccessTokenRepository');
    }

    /**
     * @return AuthorizationCodeRepositoryInterface
     */
    public function getAuthorizationCodeRepository()
    {
        return $this->getRepositoryManager()->get('Thorr\OAuth2\Repository\AuthorizationCodeRepository');
    }

    /**
     * @return ClientRepositoryInterface
     */
    public function getClientRepository()
    {
        return $this->getRepositoryManager()->get('Thorr\OAuth2\Repository\ClientRepository');
    }

    /**
     * @return RefreshTokenRepositoryInterface
     */
    public function getRefreshTokenRepository()
    {
        return $this->getRepositoryManager()->get('Thorr\OAuth2\Repository\RefreshTokenRepository');
    }

    /**
     * @return ScopeRepositoryInterface
     */
    public function getScopeRepository()
    {
        return $this->getRepositoryManager()->get('Thorr\OAuth2\Repository\ScopeRepository');
    }

    /**
     * @return UserRepositoryInterface
     */
    public function getUserRepository()
    {
        return $this->getRepositoryManager()->get('Thorr\OAuth2\Repository\UserRepository');
    }

    /**
     * @return ThirdPartyUserRepositoryInterface
     */
    public function getThirdPartyUserRepository()
    {
        return $this->getRepositoryManager()->get('Thorr\OAuth2\Repository\ThirdPartyUserRepository');
    }
}

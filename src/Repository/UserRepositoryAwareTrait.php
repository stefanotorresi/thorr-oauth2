<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Repository;

trait UserRepositoryAwareTrait
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @return UserRepositoryInterface
     */
    public function getUserRepository()
    {
        return $this->userRepository;
    }

    /**
     * @param  UserRepositoryInterface $userRepository
     * @return self
     */
    public function setUserRepository(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;

        return $this;
    }
}

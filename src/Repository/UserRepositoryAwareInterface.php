<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Repository;

interface UserRepositoryAwareInterface
{
    /**
     * @return UserRepositoryInterface
     */
    public function getUserRepository();

    /**
     * @param  UserRepositoryInterface $userRepository
     * @return self
     */
    public function setUserRepository(UserRepositoryInterface $userRepository);
}

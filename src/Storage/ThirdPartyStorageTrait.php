<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Storage;

use Thorr\OAuth\Entity\UserInterface;
use Thorr\OAuth\Repository\UserRepositoryInterface;

trait ThirdPartyStorageTrait
{
    /**
     * @param UserInterface|mixed $user
     * @return bool
     */
    public function hasUser($user)
    {
        $userId = $user instanceof UserInterface ? $user->getId() : $user;

        $persistentUser = $this->getUserRepository()->find($userId);

        return $persistentUser instanceof UserInterface;
    }

    /**
     * @param UserInterface $user
     */
    public function saveUser(UserInterface $user)
    {
        $this->getUserRepository()->save($user);
    }

    /**
     * @return UserRepositoryInterface
     */
    abstract public function getUserRepository();
}

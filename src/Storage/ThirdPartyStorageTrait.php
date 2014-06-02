<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Storage;

use Thorr\OAuth\Entity\ThirdPartyUserInterface;
use Thorr\OAuth\Entity\UserInterface;
use Thorr\OAuth\Repository\ThirdPartyUserRepositoryInterface;
use Thorr\OAuth\Repository\UserRepositoryInterface;

trait ThirdPartyStorageTrait
{
    /**
     * @param ThirdPartyUserInterface $thirdPartyUser
     * @return UserInterface|null
     */
    public function findUserByThirdParty(ThirdPartyUserInterface $thirdPartyUser)
    {
        return $this->getUserRepository()->findUserByThirdParty($thirdPartyUser);
    }

    /**
     * @param  string $id
     * @param  string $provider
     * @return mixed
     */
    public function findThirdPartyUser($id, $provider)
    {
        return $this->getThirdPartyUserRepository()->find([ 'id' => $id, 'provider' => $provider]);
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

    /**
     * @return ThirdPartyUserRepositoryInterface
     */
    abstract public function getThirdPartyUserRepository();
}

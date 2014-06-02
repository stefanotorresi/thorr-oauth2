<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Storage;

use Thorr\OAuth\Entity\ThirdPartyUserInterface;
use Thorr\OAuth\Entity\UserInterface;

interface ThirdPartyStorageInterface
{
    /**
     * @param  ThirdPartyUserInterface $thirdPartyUser
     * @return UserInterface|null
     */
    public function findUserByThirdParty(ThirdPartyUserInterface $thirdPartyUser);

    /**
     * @param  string $id
     * @param  string $provider
     * @return mixed
     */
    public function findThirdPartyUser($id, $provider);

    /**
     * @param UserInterface $user
     * @void
     */
    public function saveUser(UserInterface $user);
}

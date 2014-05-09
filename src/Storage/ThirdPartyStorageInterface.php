<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Storage;

use Thorr\OAuth\Entity\UserInterface;

interface ThirdPartyStorageInterface
{
    /**
     * @param UserInterface|mixed $user
     * @return bool
     */
    public function hasUser($user);

    /**
     * @param UserInterface $user
     * @void
     */
    public function saveUser(UserInterface $user);
}

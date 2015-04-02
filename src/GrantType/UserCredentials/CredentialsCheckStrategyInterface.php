<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\GrantType\UserCredentials;

use Thorr\OAuth2\Entity\UserInterface;

interface CredentialsCheckStrategyInterface
{
    /**
     * @param UserInterface $user
     * @param string        $password
     *
     * @return bool
     */
    public function isValid(UserInterface $user, $password);
}

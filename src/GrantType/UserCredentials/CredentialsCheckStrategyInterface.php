<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\GrantType\UserCredentials;

interface CredentialsCheckStrategyInterface
{
    public function isValid($user, $password);
}

<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\GrantType\ThirdParty\Provider;

use Thorr\OAuth\Entity\UserInterface;

interface ProviderInterface
{
    /**
     * @param $userId
     * @param $accessToken
     * @return boolean
     */
    public function validate($userId, $accessToken);

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return UserInterface
     */
    public function getUser();
}

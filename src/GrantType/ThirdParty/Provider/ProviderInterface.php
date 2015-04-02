<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\GrantType\ThirdParty\Provider;

interface ProviderInterface
{
    /**
     * @param $userId
     * @param $accessToken
     *
     * @return bool
     */
    public function validate($userId, $accessToken);

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return mixed
     */
    public function getUserId();

    /***
     * @return array
     */
    public function getUserData();
}

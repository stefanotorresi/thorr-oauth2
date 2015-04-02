<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

use Doctrine\Common\Collections\Collection;

interface ThirdPartyAwareInterface
{
    /**
     * @return array
     */
    public function getThirdPartyCredentials();

    /**
     * @param array|Collection $thirdPartyCredentials
     */
    public function setThirdPartyCredentials($thirdPartyCredentials);

    /**
     * @param ThirdParty $thirdParty
     *
     * @return bool
     */
    public function addThirdParty(ThirdParty $thirdParty);

    /**
     * @param ThirdParty $thirdParty
     *
     * @return bool
     */
    public function removeThirdParty(ThirdParty $thirdParty);

    /**
     * @param string $provider
     *
     * @return ThirdParty|null
     */
    public function findThirdPartyByProvider($provider);
}

<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

use Thorr\Persistence\Entity\IdProviderInterface;

interface UserInterface extends IdProviderInterface
{
    /**
     * Get password.
     *
     * @return string password
     */
    public function getPassword();

    /**
     * Set password.
     *
     * @param string $password
     * @return UserInterface
     */
    public function setPassword($password);

    /**
     * @param ThirdPartyUserInterface $thirdPartyUser
     * @return boolean
     */
    public function addThirdPartyUser(ThirdPartyUserInterface $thirdPartyUser);
}

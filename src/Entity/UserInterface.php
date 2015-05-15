<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

use Thorr\Persistence\Entity\UuidProviderInterface;

interface UserInterface extends UuidProviderInterface
{
    /**
     * Get password hash
     *
     * @return string password
     */
    public function getPassword();

    /**
     * Set password hash
     *
     * @param string $password
     */
    public function setPassword($password);
}

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
     * @return string
     */
    public function getUsername();

    /**
     * @param string $username
     */
    public function setUsername($username);

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
     */
    public function setPassword($password);
}

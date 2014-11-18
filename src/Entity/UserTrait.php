<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

trait UserTrait
{
    /**
     * @var string
     */
    protected $password;

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password.
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = (string) $password;
    }
}

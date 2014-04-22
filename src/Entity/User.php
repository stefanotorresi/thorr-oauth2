<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Entity;

use Thorr\Persistence\Entity\AbstractEntity;

class User extends AbstractEntity implements UserInterface
{
    use UserTrait;

    public function __construct($username = null, $password = null)
    {
        if ($username) {
            $this->setUsername($username);
        }

        if ($password) {
            $this->setPassword($password);
        }
    }
}

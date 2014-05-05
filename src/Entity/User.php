<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Entity;

use Thorr\Persistence\Entity\IdProviderTrait;

class User implements UserInterface
{
    use UserTrait;

    public function __construct($id = null, $password = null)
    {
        if ($id) {
            $this->setId($id);
        }

        if ($password) {
            $this->setPassword($password);
        }
    }
}

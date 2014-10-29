<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Thorr\Nonce\Entity\NonceOwnerInterface;
use Thorr\Persistence\Entity\IdProviderTrait;

class User implements UserInterface, NonceOwnerInterface
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

        $this->thirdPartyUsers = new ArrayCollection();
    }
}

<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

use Thorr\Persistence\Entity\IdProviderTrait;

class User implements ThirdPartyAwareUserInterface
{
    use IdProviderTrait;
    use UserTrait;
    use ThirdPartyUsersAwareTrait;

    public function __construct($id = null, $password = null)
    {
        if ($id) {
            $this->setId($id);
        }

        if ($password) {
            $this->setPassword($password);
        }

        $this->initThirdPartyCredentials();
    }
}

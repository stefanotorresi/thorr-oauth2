<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

use Rhumsaa\Uuid\Uuid;
use Thorr\Persistence\Entity\AbstractEntity;

class User extends AbstractEntity implements ThirdPartyAwareUserInterface
{
    use UserTrait;
    use ThirdPartyAwareTrait;

    public function __construct($uuid = null, $password = null)
    {
        parent::__construct($uuid);

        if ($password) {
            $this->setPassword($password);
        }

        $this->initThirdPartyCredentials();
    }
}

<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\GrantType\ThirdParty\Provider;

use LogicException;
use Zend\Stdlib\Guard\EmptyGuardTrait;

trait ProviderTrait
{
    use EmptyGuardTrait;

    /**
     * @var mixed
     */
    protected $userData;

    /**
     * @throws LogicException
     * @return array
     */
    public function getUserData()
    {
        $this->guardAgainstEmpty($this->userData);

        if (! isset($this->userData['id'])) {
            throw new LogicException('Third party user data array must have an "id" key');
        }

        return $this->userData;
    }
}

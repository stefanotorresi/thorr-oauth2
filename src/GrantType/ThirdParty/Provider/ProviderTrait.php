<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\GrantType\ThirdParty\Provider;

use Zend\Stdlib\Guard\EmptyGuardTrait;

trait ProviderTrait
{
    use EmptyGuardTrait;

    /**
     * @var mixed
     */
    protected $userId;

    /**
     * @var mixed
     */
    protected $userData;

    /**
     * @return mixed
     */
    public function getUserId()
    {
        $this->guardAgainstEmpty($this->userId);

        return $this->userId;
    }

    /**
     * @return array
     */
    public function getUserData()
    {
        $this->guardAgainstEmpty($this->userData);

        return $this->userData;
    }
}

<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $userEntityClassName = 'Thorr\OAuth\Entity\User';

    /**
     * @return string
     */
    public function getUserEntityClassName()
    {
        return $this->userEntityClassName;
    }

    /**
     * @param string $userClassName
     */
    public function setUserEntityClassName($userClassName)
    {
        $this->userEntityClassName = (string) $userClassName;
    }
}

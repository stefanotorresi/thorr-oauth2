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
     * @var int
     */
    protected $bcryptCost = 10;

    /**
     * @var bool
     */
    protected $defaultUserMappingEnabled = true;

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

    /**
     * @return int
     */
    public function getBcryptCost()
    {
        return $this->bcryptCost;
    }

    /**
     * @param int $bcryptCost
     */
    public function setBcryptCost($bcryptCost)
    {
        $this->bcryptCost = (int) $bcryptCost;
    }

    /**
     * @return boolean
     */
    public function isDefaultUserMappingEnabled()
    {
        return $this->defaultUserMappingEnabled;
    }

    /**
     * @param boolean $loadDefaultUserMapping
     */
    public function setDefaultUserMappingEnabled($loadDefaultUserMapping)
    {
        $this->defaultUserMappingEnabled = (bool) $loadDefaultUserMapping;
    }
}

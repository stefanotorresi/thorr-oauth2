<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Options;

use InvalidArgumentException;
use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $userEntityClassName = 'Thorr\OAuth2\Entity\User';

    /**
     * @var int
     */
    protected $bcryptCost = 10;

    /**
     * @var bool
     */
    protected $thirdPartyGrantTypeEnabled = false;

    /**
     * @var array
     */
    protected $thirdPartyProviders = [];

    /**
     * @return string
     */
    public function getUserEntityClassName()
    {
        return $this->userEntityClassName;
    }

    /**
     * @param string $userClassName
     * @throws InvalidArgumentException
     */
    public function setUserEntityClassName($userClassName)
    {
        if (! class_exists($userClassName)) {
            throw new InvalidArgumentException('User class does not exist');
        }

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
    public function isThirdPartyGrantTypeEnabled()
    {
        return $this->thirdPartyGrantTypeEnabled;
    }

    /**
     * @param boolean $thirdPartyProvidersEnabled
     */
    public function setThirdPartyGrantTypeEnabled($thirdPartyProvidersEnabled)
    {
        $this->thirdPartyGrantTypeEnabled = $thirdPartyProvidersEnabled;
    }

    /**
     * @return array
     */
    public function getThirdPartyProviders()
    {
        return $this->thirdPartyProviders;
    }

    /**
     * @param array $thirdPartyProviders
     */
    public function setThirdPartyProviders($thirdPartyProviders)
    {
        $this->thirdPartyProviders = $thirdPartyProviders;
    }
}

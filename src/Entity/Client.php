<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Entity;

use Thorr\Persistence\Entity\IdProviderInterface;
use Thorr\Persistence\Entity\IdProviderTrait;

class Client implements
    IdProviderInterface,
    ScopesProviderInterface
{
    use IdProviderTrait;
    use RedirectUriProviderTrait;
    use ScopesProviderTrait;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var array
     */
    protected $grantTypes = [];

    /**
     *
     */
    public function __construct()
    {
        $this->initScopes();
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     * @return self
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return empty($this->secret);
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param UserInterface $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function getGrantTypes()
    {
        return $this->grantTypes;
    }

    /**
     * @param array $grantTypes
     */
    public function setGrantTypes($grantTypes)
    {
        $this->grantTypes = $grantTypes;
    }
}

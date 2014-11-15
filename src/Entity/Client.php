<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

use Thorr\Persistence\Entity\AbstractEntity;

class Client extends AbstractEntity implements ScopesProviderInterface
{
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
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
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

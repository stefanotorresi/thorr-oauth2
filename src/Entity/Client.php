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
     * @var string
     */
    protected $description;

    /**
     * {@inheritdoc}
     *
     * @param string        $secret
     * @param UserInterface $user
     * @param string[]      $grantTypes
     * @param string        $redirectUri
     * @param string        $description
     */
    public function __construct($uuid = null, $secret = null, $user = null, $grantTypes = null, $redirectUri = null, $description = null)
    {
        parent::__construct($uuid);

        if ($secret) {
            $this->setSecret($secret);
        }

        if ($user) {
            $this->setUser($user);
        }

        if ($grantTypes) {
            $this->setGrantTypes($grantTypes);
        }

        if ($redirectUri) {
            $this->setRedirectUri($redirectUri);
        }

        if ($description) {
            $this->setDescription($description);
        }

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
        $this->secret = (string) $secret;
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
    public function setUser(UserInterface $user = null)
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
     * @param string[] $grantTypes
     */
    public function setGrantTypes(array $grantTypes)
    {
        $this->grantTypes = $grantTypes;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = (string) $description;
    }
}

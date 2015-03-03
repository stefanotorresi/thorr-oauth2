<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

use Rhumsaa\Uuid\Uuid;
use Thorr\Persistence\Entity\AbstractEntity;

class ThirdParty extends AbstractEntity
{
    /**
     * @var mixed
     */
    protected $providerUserId;

    /**
     * @var string
     */
    protected $provider;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var array
     */
    protected $data;

    /**
     * {@inheritdoc}
     * @param mixed         $providerUserId
     * @param string        $provider
     * @param UserInterface $user
     * @param array         $data
     */
    public function __construct($uuid = null, $providerUserId, $provider, UserInterface $user, $data = [])
    {
        parent::__construct($uuid);

        $this->setProviderUserId($providerUserId);
        $this->setProvider($provider);
        $this->setUser($user);
        $this->setData($data);
    }

    /**
     * @return mixed
     */
    public function getProviderUserId()
    {
        return $this->providerUserId;
    }

    /**
     * @param mixed $providerUserId
     */
    public function setProviderUserId($providerUserId)
    {
        $this->providerUserId = $providerUserId;
    }

    /**
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
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
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = (array) $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}

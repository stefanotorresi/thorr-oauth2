<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections;
use Thorr\Persistence\Entity\IdProviderInterface;
use Thorr\Persistence\Entity\IdProviderTrait;

abstract class AbstractToken implements IdProviderInterface, ScopesProviderInterface
{
    use IdProviderTrait
    use ScopesProviderTrait;
    use ExpiryDateProviderTrait;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     *
     */
    public function __construct()
    {
        $this->initScopes();
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = (string) $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}

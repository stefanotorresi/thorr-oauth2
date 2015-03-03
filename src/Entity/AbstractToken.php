<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

use Doctrine\Common\Collections\Collection;
use Rhumsaa\Uuid\Uuid;
use Thorr\Persistence\Entity\AbstractEntity;

abstract class AbstractToken extends AbstractEntity implements ScopesProviderInterface
{
    use ScopesProviderTrait;
    use ExpiryDateProviderTrait;

    /**
     * The token string; must be unique
     *
     * @var string
     */
    protected $token;

    /**
     * A token always belongs to a particular client
     *
     * @var Client
     */
    protected $client;

    /**
     * A token may belong to a particular user
     *
     * @var UserInterface
     */
    protected $user;

    /**
     * {@inheritdoc}
     * @param string           $token
     * @param Client           $client
     * @param UserInterface    $user
     * @param array|Collection $scopes
     */
    public function __construct($uuid = null, $token, Client $client, UserInterface $user = null, $scopes = null)
    {
        parent::__construct($uuid);

        $this->setToken($token);
        $this->setClient($client);

        if ($user) {
            $this->setUser($user);
        }

        if ($scopes) {
            $this->setScopes($scopes);
        } else {
            $this->initScopes();
        }
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
    public function setUser(UserInterface $user = null)
    {
        $this->user = $user;
    }

    /**
     * @return UserInterface|null
     */
    public function getUser()
    {
        return $this->user;
    }
}

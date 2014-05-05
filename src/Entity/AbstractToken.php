<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Entity;

use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections;

abstract class AbstractToken
{
    use ScopesProviderTrait;

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
     * @var DateTime
     */
    protected $expirationDate;

    /**
     *
     */
    public function __construct()
    {
        $this->initScopes();
    }

    /**
     * @param Client $client
     * @return self
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param DateTime $expirationDate
     * @return self
     */
    public function setExpirationDate($expirationDate)
    {
        if ($expirationDate instanceof DateTime) {
            $expirationDate->setTimezone(new DateTimeZone(date_default_timezone_get()));
        }

        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->expirationDate !== null && $this->expirationDate < new DateTime('now');
    }

    /**
     * @return int
     */
    public function getExpirationUTCTimestamp()
    {
        if (! $this->expirationDate) {
            return 0;
        }

        if ($this->expirationDate->getTimezone()->getOffset($this->expirationDate) === 0) {
            return $this->expirationDate->getTimestamp();
        }

        $utcDate = clone $this->expirationDate;
        $utcDate->setTimezone(new DateTimeZone('UTC'));

        return $utcDate->getTimestamp();
    }

    /**
     * @param string $token
     * @return self
     */
    public function setToken($token)
    {
        $this->token = (string) $token;

        return $this;
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
     * @return self
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}

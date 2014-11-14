<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

use DateTime;
use DateTimeZone;

trait ExpiryDateProviderTrait
{
    /**
     * @var DateTime
     */
    protected $expiryDate;

    /**
     * @param DateTime $expiryDate
     */
    public function setExpiryDate($expiryDate)
    {
        if ($expiryDate instanceof DateTime) {
            $expiryDate->setTimezone(new DateTimeZone(date_default_timezone_get()));
        }

        $this->expiryDate = $expiryDate;
    }

    /**
     * @return DateTime
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->expiryDate !== null && $this->expiryDate < new DateTime('now');
    }

    /**
     * @return int
     */
    public function getExpiryUTCTimestamp()
    {
        if (! $this->expiryDate) {
            return 0;
        }

        if ($this->expiryDate->getTimezone()->getOffset($this->expiryDate) === 0) {
            return $this->expiryDate->getTimestamp();
        }

        $utcDate = clone $this->expiryDate;
        $utcDate->setTimezone(new DateTimeZone('UTC'));

        return $utcDate->getTimestamp();
    }
}

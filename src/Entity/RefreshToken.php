<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Entity;

class RefreshToken extends AbstractToken
{
    /**
     * {@inheritDoc}
     */
    public function isExpired()
    {
        return $this->expirationDate !== null && parent::isExpired();
    }
}

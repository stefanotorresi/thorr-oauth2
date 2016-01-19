<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\DataMapper;

use Thorr\OAuth2\Entity\AbstractToken;
use Thorr\Persistence\DataMapper\EntityRemoverInterface;
use Thorr\Persistence\DataMapper\EntitySaverInterface;

interface TokenMapperInterface extends EntitySaverInterface, EntityRemoverInterface
{
    /**
     * @param string $token
     *
     * @return AbstractToken|null
     */
    public function findByToken($token);
}

<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\DataMapper;

use Thorr\OAuth2\Entity\UserInterface;
use Thorr\Persistence\DataMapper\SimpleDataMapperInterface;

interface UserMapperInterface extends SimpleDataMapperInterface
{
    /**
     * @param string $credential may be any unique field value allowed as a login name
     *
     * @return UserInterface|null
     */
    public function findByCredential($credential);
}

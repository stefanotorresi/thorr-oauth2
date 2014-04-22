<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Repository;

use Thorr\OAuth\Entity\UserInterface;

interface UserRepositoryInterface
{
    /**
     * @param  string $username
     * @return UserInterface|null
     */
    public function findOneByUsername($username);
}

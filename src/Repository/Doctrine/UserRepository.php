<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Repository\Doctrine;

use Thorr\OAuth\Entity\UserInterface;
use Thorr\OAuth\Repository\UserRepositoryInterface;
use Thorr\Persistence\Doctrine\Repository\EntityRepository;

class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    /**
     * @param  string $username
     * @return UserInterface|null
     */
    public function findOneByUsername($username)
    {
        return parent::findOneBy(['username' => $username]);
    }
}

<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Doctrine\Repository;

use Doctrine\ORM\NoResultException;
use Thorr\OAuth\Entity\ThirdPartyUserInterface;
use Thorr\OAuth\Entity\UserInterface;
use Thorr\OAuth\Repository\UserRepositoryInterface;
use Thorr\Persistence\Doctrine\Repository\EntityRepository;

class UserRepository extends EntityRepository implements UserRepositoryInterface
{

}

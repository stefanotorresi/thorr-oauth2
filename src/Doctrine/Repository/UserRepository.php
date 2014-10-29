<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Doctrine\Repository;

use Doctrine\ORM\NoResultException;
use Thorr\OAuth2\Entity\ThirdPartyUserInterface;
use Thorr\OAuth2\Entity\UserInterface;
use Thorr\OAuth2\Repository\UserRepositoryInterface;
use Thorr\Persistence\Doctrine\Repository\EntityRepository;

class UserRepository extends EntityRepository implements UserRepositoryInterface
{

}

<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Doctrine\Repository;

use Thorr\OAuth\Repository\AccessTokenRepositoryInterface;
use Thorr\Persistence\Doctrine\Repository\EntityRepository;

class AccessTokenRepository extends EntityRepository implements AccessTokenRepositoryInterface
{

}

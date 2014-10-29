<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Doctrine\Repository;

use Thorr\OAuth2\Repository\RefreshTokenRepositoryInterface;
use Thorr\Persistence\Doctrine\Repository\EntityRepository;

class RefreshTokenRepository extends EntityRepository implements RefreshTokenRepositoryInterface
{

}

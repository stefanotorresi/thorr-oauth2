<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Doctrine\Repository;

use Thorr\OAuth\Repository\ThirdPartyUserRepositoryInterface;
use Thorr\Persistence\Doctrine\Repository\EntityRepository;

class ThirdPartyUserRepository extends EntityRepository implements ThirdPartyUserRepositoryInterface
{

}

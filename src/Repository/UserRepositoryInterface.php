<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Repository;

use Thorr\OAuth\Entity\ThirdPartyUserInterface;
use Thorr\OAuth\Entity\UserInterface;
use Thorr\Persistence\Repository\RepositoryInterface;

/**
 * Interface UserRepositoryInterface
 * @package Thorr\OAuth\Repository
 * @method UserInterface|null find($userId)
 */
interface UserRepositoryInterface extends RepositoryInterface
{

}

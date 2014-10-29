<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Repository;

use Thorr\OAuth2\Entity\ThirdPartyUserInterface;
use Thorr\OAuth2\Entity\UserInterface;
use Thorr\Persistence\Repository\RepositoryInterface;

/**
 * Interface UserRepositoryInterface
 * @package Thorr\OAuth2\Repository
 * @method UserInterface|null find($userId)
 */
interface UserRepositoryInterface extends RepositoryInterface
{

}

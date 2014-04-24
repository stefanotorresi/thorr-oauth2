<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Repository;

use Thorr\OAuth\Entity\AccessToken;
use Thorr\Persistence\Repository\RepositoryInterface;

/**
 * Interface AccessTokenRepositoryInterface
 * @package Thorr\OAuth\Repository
 * @method AccessToken|null find($token)
 */
interface AccessTokenRepositoryInterface extends RepositoryInterface
{

}

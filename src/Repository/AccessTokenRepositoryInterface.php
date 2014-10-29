<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Repository;

use Thorr\OAuth2\Entity\AccessToken;
use Thorr\Persistence\Repository\RepositoryInterface;

/**
 * Interface AccessTokenRepositoryInterface
 * @package Thorr\OAuth2\Repository
 * @method AccessToken|null find($token)
 */
interface AccessTokenRepositoryInterface extends RepositoryInterface
{

}

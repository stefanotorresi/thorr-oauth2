<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Repository;

use Thorr\OAuth2\Entity\AuthorizationCode;
use Thorr\Persistence\Repository\RepositoryInterface;

/**
 * Interface AuthorizationCodeRepositoryInterface
 * @package Thorr\OAuth2\Repository
 * @method AuthorizationCode|null find($code)
 */
interface AuthorizationCodeRepositoryInterface extends RepositoryInterface
{

}

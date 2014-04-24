<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Repository;

use Thorr\OAuth\Entity\AuthorizationCode;
use Thorr\Persistence\Repository\RepositoryInterface;

/**
 * Interface AuthorizationCodeRepositoryInterface
 * @package Thorr\OAuth\Repository
 * @method AuthorizationCode|null find($code)
 */
interface AuthorizationCodeRepositoryInterface extends RepositoryInterface
{

}

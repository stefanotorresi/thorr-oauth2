<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Repository;

use Thorr\OAuth\Entity\RefreshToken;
use Thorr\Persistence\Repository\RepositoryInterface;

/**
 * Interface RefreshTokenRepositoryInterface
 * @package Thorr\OAuth\Repository
 * @method RefreshToken|null find($token)
 */
interface RefreshTokenRepositoryInterface extends RepositoryInterface
{

}

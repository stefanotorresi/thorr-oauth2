<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Repository;

use Thorr\OAuth2\Entity\ThirdPartyUserInterface;
use Thorr\Persistence\Repository\RepositoryInterface;

/**
 * Interface ThirdPartyUserRepositoryInterface
 * @package Thorr\OAuth2\Repository
 * @method ThirdPartyUserInterface|null find($compositeId)
 */
interface ThirdPartyUserRepositoryInterface extends RepositoryInterface
{

}

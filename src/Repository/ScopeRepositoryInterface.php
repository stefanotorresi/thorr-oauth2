<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Repository;

use Thorr\Persistence\Repository\RepositoryInterface;
use Traversable;

interface ScopeRepositoryInterface extends RepositoryInterface
{
    /**
     * @param array|Traversable $scopes
     * @return array
     */
    public function findScopes($scopes);

    /**
     * @return array
     */
    public function findDefaultScopes();
}

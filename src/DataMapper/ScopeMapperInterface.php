<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\DataMapper;

use Thorr\Persistence\DataMapper\DataMapperInterface;
use Traversable;

interface ScopeMapperInterface extends DataMapperInterface
{
    /**
     * @param array|Traversable $scopes An array of scope names
     * @return array                    An array of Scope instances
     */
    public function findScopes($scopes);

    /**
     * @return array An array of Scope instances
     */
    public function findDefaultScopes();
}

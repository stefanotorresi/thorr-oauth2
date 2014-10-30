<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\DataMapper;

use Traversable;

interface ScopeMapperInterface extends DataMapperInterface
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

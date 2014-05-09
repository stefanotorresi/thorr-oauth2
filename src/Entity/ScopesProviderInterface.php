<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Entity;

use Doctrine\Common\Collections\Collection;

interface ScopesProviderInterface
{
    /**
     * @return Collection
     */
    public function getScopes();

    /**
     * @return string
     */
    public function getScopesString();

    /**
     * @param  Collection|array $scopes
     * @return self
     */
    public function setScopes($scopes);
}

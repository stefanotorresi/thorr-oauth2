<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

use Doctrine\Common\Collections\Collection;

interface ScopesProviderInterface
{
    /**
     * @return array
     */
    public function getScopes();

    /**
     * @return string
     */
    public function getScopesString();

    /**
     * @param Collection|array $scopes
     */
    public function setScopes($scopes);
}

<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Entity;

use Doctrine\Common\Collections;

trait ScopesProviderTrait
{
    /**
     * @var Collections\Collection
     */
    protected $scopes;

    /**
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes->toArray();
    }

    /**
     * @return string
     */
    public function getScopesString()
    {
        return implode(' ', $this->getScopes());
    }

    /**
     * @param  Collections\Collection|array $scopes
     * @return self
     */
    public function setScopes($scopes)
    {
        if (! $scopes instanceof Collections\Collection) {
            $scopes = new Collections\ArrayCollection($scopes);
        }

        $this->scopes = $scopes;

        return $this;
    }

    /**
     * initializes an empty array collection, required by Doctrine
     * call this in the constructor of each class implementing this trait
     */
    private function initScopes()
    {
        $this->scopes = new Collections\ArrayCollection();
    }
}

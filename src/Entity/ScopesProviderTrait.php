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
     * @return Collections\Collection
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @param  Collections\Collection $scopes
     * @return self
     */
    public function setScopes(Collections\Collection $scopes)
    {
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

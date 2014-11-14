<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

use Doctrine\Common\Collections;

/**
 * Don't forget to initialize $thirdPartyUsers property in the constructor
 */
trait ThirdPartyUsersAwareTrait
{
    /**
     * @var Collections\Collection
     */
    protected $thirdPartyCredentials;

    /**
     * @return array
     */
    public function getThirdPartyCredentials()
    {
        return $this->thirdPartyCredentials->toArray();
    }

    /**
     * @param array|Collections\Collection $thirdPartyUsers
     */
    public function setThirdPartyCredentials($thirdPartyUsers)
    {
        if (! $thirdPartyUsers instanceof Collections\Collection) {
            $thirdPartyUsers = new Collections\ArrayCollection($thirdPartyUsers);
        }
        $this->thirdPartyCredentials = $thirdPartyUsers;
    }

    /**
     * @param ThirdParty $thirdParty
     * @return bool
     */
    public function addThirdParty(ThirdParty $thirdParty)
    {
        if ($this->thirdPartyCredentials->contains($thirdParty)) {
            return false;
        }

        return $this->thirdPartyCredentials->add($thirdParty);
    }

    /**
     * @param ThirdParty $thirdParty
     * @return bool
     */
    public function removeThirdParty(ThirdParty $thirdParty)
    {
        return (bool) $this->thirdPartyCredentials->remove($thirdParty);
    }

    /**
     * @param  string $provider
     * @return ThirdParty|null
     */
    public function findThirdPartyByProvider($provider)
    {
        foreach ($this->thirdPartyCredentials as $thirdParty) { /** @var ThirdParty $thirdParty */
            if ($thirdParty->getProvider() === $provider) {
                return $thirdParty;
            };
        }

        return null;
    }

    /**
     * initializes an empty array collection
     * call this in the constructor of each class implementing this trait
     */
    private function initThirdPartyCredentials()
    {
        $this->thirdPartyCredentials = new Collections\ArrayCollection();
    }
}

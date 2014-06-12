<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Thorr\Persistence\Entity\IdProviderTrait;

trait UserTrait
{
    use IdProviderTrait;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var Collection
     */
    protected $thirdPartyUsers;

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password.
     *
     * @param string $password
     * @return UserInterface
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return array
     */
    public function getThirdPartyUsers()
    {
        return $this->thirdPartyUsers->toArray();
    }

    /**
     * @param array|Collection $thirdPartyUsers
     */
    public function setThirdPartyUsers($thirdPartyUsers)
    {
        if (! $thirdPartyUsers instanceof Collection) {
            $thirdPartyUsers = new ArrayCollection($thirdPartyUsers);
        }
        $this->thirdPartyUsers = $thirdPartyUsers;
    }

    /**
     * @param ThirdPartyUserInterface $thirdPartyUser
     * @return bool
     */
    public function addThirdPartyUser(ThirdPartyUserInterface $thirdPartyUser)
    {
        if ($this->thirdPartyUsers->contains($thirdPartyUser)) {
            return false;
        }
        return $this->thirdPartyUsers->add($thirdPartyUser);
    }

    /**
     * @param ThirdPartyUserInterface $thirdPartyUser
     * @return bool
     */
    public function removeThirdPartyUser(ThirdPartyUserInterface $thirdPartyUser)
    {
        return (bool) $this->thirdPartyUsers->remove($thirdPartyUser);
    }

    /**
     * @param  string $provider
     * @return ThirdPartyUserInterface|null
     */
    public function findThirdPartyUser($provider)
    {
        foreach ($this->thirdPartyUsers as $thirdPartyUser) { /** @var ThirdPartyUserInterface $thirdPartyUser */
            if ($thirdPartyUser->getProvider() === $provider) {
                return $thirdPartyUser;
            };
        }

        return null;
    }
}

<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\GrantType\ThirdParty\Provider;

use DomainException;
use LogicException;
use Thorr\OAuth\Entity\UserInterface;

trait ProviderTrait
{
    /**
     * @var callable
     */
    protected $userFactory;

    /**
     * @var mixed
     */
    protected $userData;

    /**
     * @throws LogicException
     * @return UserInterface
     */
    public function getUser()
    {
        if (! $this->userData) {
            throw new LogicException('User validation didn\'t occur or pass');
        }

        $user = call_user_func($this->userFactory, $this->userData);

        if (! $user instanceof UserInterface) {
            throw new DomainException('the UserFactory callable must return a UserInterface');
        }

        return $user;
    }

    /**
     * @return callable
     */
    public function getUserFactory()
    {
        return $this->userFactory;
    }

    /**
     * @param callable $userFactory
     */
    public function setUserFactory(callable $userFactory)
    {
        $this->userFactory = $userFactory;
    }

}

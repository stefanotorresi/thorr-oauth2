<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\GrantType\UserCredentials;

use Thorr\OAuth2\Entity\UserInterface;
use Zend\Crypt\Password\PasswordInterface;

class PasswordStrategy implements UserCredentialsStrategyInterface
{
    /**
     * @var PasswordInterface
     */
    protected $password;

    /**
     * @param PasswordInterface $password
     */
    public function __construct(PasswordInterface $password)
    {
        $this->password = $password;
    }

    /**
     * @param UserInterface $user
     * @param string        $password
     *
     * @return bool
     */
    public function isValid(UserInterface $user, $password)
    {
        return $this->password->verify($password, $user->getPassword());
    }
}

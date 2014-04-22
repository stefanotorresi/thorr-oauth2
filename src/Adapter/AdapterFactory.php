<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Adapter;

use Thorr\OAuth\Repository\UserRepositoryInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AdapterFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Adapter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var UserRepositoryInterface $userRepository */
        $userRepository  = $serviceLocator->get('Thorr\OAuth\Repository\UserRepository');

        $adapter = new Adapter($userRepository);

        return $adapter;
    }
}

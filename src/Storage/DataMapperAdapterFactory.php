<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Storage;

use Thorr\OAuth\Options\ModuleOptions;
use Thorr\OAuth\Repository;
use Zend\Crypt\Password\Bcrypt;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DataMapperAdapterFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return DataMapperAdapter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var Repository\UserRepositoryInterface $userRepository */
        $userRepository         = $serviceLocator->get('Thorr\OAuth\Repository\UserRepository');

        /** @var Repository\AccessTokenRepositoryInterface $accessTokenRepository */
        $accessTokenRepository  = $serviceLocator->get('Thorr\OAuth\Repository\AccessTokenRepository');

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions          = $serviceLocator->get('Thorr\OAuth\Options\ModuleOptions');

        $bcrypt = new Bcrypt([
           'cost' => $moduleOptions->getBcryptCost()
        ]);

        $adapter = new DataMapperAdapter($bcrypt, $accessTokenRepository, $userRepository);

        return $adapter;
    }
}

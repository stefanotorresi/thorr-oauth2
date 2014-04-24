<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Storage;

use Thorr\OAuth\Options\ModuleOptions;
use Thorr\OAuth\Repository;
use Thorr\Persistence\Repository\Manager\RepositoryManager;
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
        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $serviceLocator->get('Thorr\Persistence\Repository\Manager\RepositoryManager');

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceLocator->get('Thorr\OAuth\Options\ModuleOptions');

        $bcrypt = new Bcrypt([
           'cost' => $moduleOptions->getBcryptCost()
        ]);

        $adapter = new DataMapperAdapter($bcrypt, $repositoryManager);

        return $adapter;
    }
}

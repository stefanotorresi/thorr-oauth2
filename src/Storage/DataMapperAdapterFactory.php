<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Storage;

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

        /** @var Bcrypt $bcrypt */
        $password = $serviceLocator->get('Thorr\OAuth\Password\Bcrypt');

        $adapter = new DataMapperAdapter($password, $repositoryManager);

        return $adapter;
    }
}

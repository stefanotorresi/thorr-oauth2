<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Storage;

use Thorr\OAuth2\Repository;
use Thorr\Persistence\DataMapper\Manager\DataMapperManager;
use Zend\Crypt\Password\Bcrypt;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DataMapperAdapterFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return DataMapperAdapter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var DataMapperManager $dataMapperManager */
        $dataMapperManager = $serviceLocator->get('Thorr\Persistence\Repository\Manager\RepositoryManager');

        /** @var Bcrypt $bcrypt */
        $password = $serviceLocator->get('Thorr\OAuth2\Password\Bcrypt');

        $adapter = new DataMapperAdapter($password, $dataMapperManager);

        return $adapter;
    }
}

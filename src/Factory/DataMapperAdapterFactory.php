<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Factory;

use Thorr\OAuth2\Options\ModuleOptions;
use Thorr\OAuth2\Storage\DataMapperAdapter;
use Thorr\Persistence\DataMapper\Manager\DataMapperManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DataMapperAdapterFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return DataMapperAdapter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var DataMapperManager $dataMapperManager */
        $dataMapperManager = $serviceLocator->get(DataMapperManager::class);

        $password = $serviceLocator->get('Thorr\OAuth2\DefaultPasswordInterface');

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceLocator->get(ModuleOptions::class);

        $adapter = new DataMapperAdapter($dataMapperManager, $password);
        $adapter->setUserClass($moduleOptions->getUserEntityClassName());

        return $adapter;
    }
}

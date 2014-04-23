<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use MyBase\AbstractModule;
use Zend\ModuleManager\Feature;
use Zend\Mvc\MvcEvent;

class Module extends AbstractModule
{
    public function onBootstrap(MvcEvent $event)
    {
        $application    = $event->getApplication();
        $serviceManager = $application->getServiceManager();

        /** @var Options\ModuleOptions $options */
        $options = $serviceManager->get('Thorr\OAuth\Options\ModuleOptions');

        // default User entity mapping is opt-out, as it will be overridden in most cases
        if ($options->isDefaultUserMappingEnabled()) {
            /** @var MappingDriverChain $doctrineDriverChain */
            $doctrineDriverChain = $serviceManager->get('doctrine.driver.orm_default');
            $doctrineDriverChain->addDriver(
                new XmlDriver(__DIR__ . '/../config/mappings', '.dcm.optional.xml'),
                'Thorr\OAuth\Entity\User'
            );
        }
    }
}

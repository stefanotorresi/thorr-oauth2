<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Server;

use OAuth2\Server as OAuth2Server;
use Thorr\OAuth\GrantType\ThirdParty;
use Thorr\OAuth\Options\ModuleOptions;
use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ServerDecorator implements DelegatorFactoryInterface
{

    /**
     * A factory that creates delegates of a given service
     *
     * @param ServiceLocatorInterface $serviceLocator the service locator which requested the service
     * @param string $name the normalized service name
     * @param string $requestedName the requested service name
     * @param callable $callback the callback that is responsible for creating the service
     *
     * @throws \RuntimeException
     * @return OAuth2Server
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {

        /** @var OAuth2Server $oauth2Server */
        $oauth2Server = $callback();

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceLocator->get('Thorr\OAuth\Options\ModuleOptions');

        $thirdPartyProviders = $moduleOptions->getThirdPartyProviders();

        if ($moduleOptions->isThirdPartyGrantTypeEnabled() && ! empty($thirdPartyProviders)) {
            /** @var ThirdParty $thirdParty */
            $thirdParty = $serviceLocator->get('Thorr\OAuth\GrantType\ThirdParty');

            $oauth2Server->addGrantType($thirdParty);
        }

        return $oauth2Server;
    }
}

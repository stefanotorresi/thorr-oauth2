<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Server;

use OAuth2\Server as OAuth2Server;
use Thorr\OAuth2\GrantType\ThirdPartyGrantType;
use Thorr\OAuth2\Options\ModuleOptions;
use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ServerInitializer implements DelegatorFactoryInterface
{
    /**
     * A factory that creates delegates of a given service
     *
     * @param ServiceLocatorInterface $serviceLocator the service locator which requested the service
     * @param string                  $name           the normalized service name
     * @param string                  $requestedName  the requested service name
     * @param callable                $callback       the callback that is responsible for creating the service
     *
     * @throws \RuntimeException
     *
     * @return OAuth2Server
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        /** @var OAuth2Server|callable $oauth2Server */
        $oauth2Server = $callback();

        /* ensure compatibility with https://github.com/zfcampus/zf-oauth2/pull/93 */
        if (is_callable($oauth2Server)) {
            $oauth2ServerFactory = $oauth2Server;
            $oauth2Server        = $oauth2ServerFactory();
        }

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceLocator->get(ModuleOptions::class);

        $thirdPartyProviders = $moduleOptions->getThirdPartyProviders();

        if ($moduleOptions->isThirdPartyGrantTypeEnabled() && ! empty($thirdPartyProviders)) {
            /** @var ThirdPartyGrantType $thirdPartyGrant */
            $thirdPartyGrant = $serviceLocator->get(ThirdPartyGrantType::class);

            $oauth2Server->addGrantType($thirdPartyGrant);
        }

        return isset($oauth2ServerFactory) ? $oauth2ServerFactory : $oauth2Server;
    }
}

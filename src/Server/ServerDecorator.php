<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Server;

use OAuth2\Server as OAuth2Server;
use Thorr\OAuth\GrantType\ThirdParty;
use Thorr\OAuth\Storage\ThirdPartyProviderInterface;
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
        $config   = $serviceLocator->get('Config');

        if (!isset($config['zf-oauth2']['storage']) || empty($config['zf-oauth2']['storage'])) {
            throw new \RuntimeException(
                'The storage configuration [\'zf-oauth2\'][\'storage\'] for OAuth2 is missing'
            );
        }

        $storage = $serviceLocator->get($config['zf-oauth2']['storage']);

        /** @var OAuth2Server $oauth2Server */
        $oauth2Server = $callback();

        if (! $storage instanceof ThirdPartyProviderInterface) {
            return $oauth2Server;
        }

        $oauth2Server->addGrantType(new ThirdParty($storage));

        return $oauth2Server;
    }
}

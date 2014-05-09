<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\GrantType\ThirdParty;

use RuntimeException;
use Thorr\OAuth\GrantType\ThirdParty;
use Thorr\OAuth\Options\ModuleOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ServiceFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @throws RuntimeException
     * @return ThirdParty
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config   = $serviceLocator->get('Config');

        if (!isset($config['zf-oauth2']['storage']) || empty($config['zf-oauth2']['storage'])) {
            throw new RuntimeException(
                'The storage configuration [\'zf-oauth2\'][\'storage\'] for OAuth2 is missing'
            );
        }

        $storage = $serviceLocator->get($config['zf-oauth2']['storage']);

        $grantType = new ThirdParty($storage);

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceLocator->get('Thorr\OAuth\Options\ModuleOptions');

        foreach ($moduleOptions->getThirdPartyProviders() as $providerConfig) {
            $provider = Provider\ProviderFactory::createProvider($providerConfig);
            $grantType->addProvider($provider);
        }

        return $grantType;
    }
}

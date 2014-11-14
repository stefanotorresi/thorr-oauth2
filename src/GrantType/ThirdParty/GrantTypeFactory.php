<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\GrantType\ThirdParty;

use RuntimeException;
use Thorr\OAuth2\DataMapper\ThirdPartyMapperInterface;
use Thorr\OAuth2\DataMapper\TokenMapperInterface;
use Thorr\OAuth2\Entity;
use Thorr\OAuth2\GrantType\ThirdPartyGrantType;
use Thorr\OAuth2\Options\ModuleOptions;
use Thorr\Persistence\DataMapper\Manager\DataMapperManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GrantTypeFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @throws RuntimeException
     * @return ThirdPartyGrantType
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var DataMapperManager $dataMapperManager */
        $dataMapperManager = $serviceLocator->get(DataMapperManager::class);

        $userMapper        = $dataMapperManager->getDataMapperForEntity(Entity\UserInterface::class);

        /** @var ThirdPartyMapperInterface $thirdPartyMapper */
        $thirdPartyMapper  = $dataMapperManager->getDataMapperForEntity(Entity\ThirdParty::class);

        /** @var TokenMapperInterface $accessTokenMapper */
        $accessTokenMapper = $dataMapperManager->getDataMapperForEntity(Entity\AccessToken::class);

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceLocator->get(ModuleOptions::class);

        $grantType = new ThirdPartyGrantType($userMapper, $thirdPartyMapper, $accessTokenMapper, $moduleOptions);

        return $grantType;
    }
}

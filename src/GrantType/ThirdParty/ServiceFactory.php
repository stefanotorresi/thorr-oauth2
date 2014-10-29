<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\GrantType\ThirdParty;

use RuntimeException;
use Thorr\OAuth2\GrantType\ThirdParty;
use Thorr\OAuth2\Options\ModuleOptions;
use Thorr\OAuth2\Repository\AccessTokenRepositoryInterface;
use Thorr\OAuth2\Repository\ThirdPartyUserRepositoryInterface;
use Thorr\OAuth2\Repository\UserRepositoryInterface;
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
        $repositoryManager = $serviceLocator->get('Thorr\Persistence\Repository\Manager\RepositoryManager');

        /** @var UserRepositoryInterface            $userRepository */
        $userRepository           = $repositoryManager->get('Thorr\OAuth2\Repository\UserRepository');

        /** @var ThirdPartyUserRepositoryInterface  $thirdPartyUserRepository */
        $thirdPartyUserRepository = $repositoryManager->get('Thorr\OAuth2\Repository\ThirdPartyUserRepository');

        /** @var AccessTokenRepositoryInterface     $accessTokenRepository */
        $accessTokenRepository    = $repositoryManager->get('Thorr\OAuth2\Repository\AccessTokenRepository');

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceLocator->get('Thorr\OAuth2\Options\ModuleOptions');

        $grantType = new ThirdParty(
            $userRepository,
            $thirdPartyUserRepository,
            $accessTokenRepository,
            $moduleOptions
        );

        return $grantType;
    }
}

<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Doctrine\Repository;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\Exception\InvalidServiceNameException;
use Zend\ServiceManager\ServiceLocatorInterface;

class AbstractRepositoryFactory implements AbstractFactoryInterface
{
    protected $repositoryClasses = [
        'Thorr\OAuth\Repository\AuthorizationCodeRepository',
        'Thorr\OAuth\Repository\AccessTokenRepository',
        'Thorr\OAuth\Repository\ClientRepository',
        'Thorr\OAuth\Repository\RefreshTokenRepository',
        'Thorr\OAuth\Repository\ScopeRepository',
    ];

    /**
     * {@inheritdoc}
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return in_array($requestedName, $this->repositoryClasses);
    }

    /**
     * {@inheritdoc}
     * @throws InvalidServiceNameException
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (! preg_match('/Thorr\\\\OAuth\\\\Repository\\\\([a-zA-Z]+?)(Repository)?$/', $requestedName, $matches)) {
            throw new InvalidServiceNameException();
        }

        $entityClass = 'Thorr\\OAuth\\Entity\\' . $matches[1];

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $serviceLocator->get('Doctrine\ORM\EntityManager');

        return $entityManager->getRepository($entityClass);
    }
}

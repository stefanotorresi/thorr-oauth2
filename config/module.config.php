<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

return [

    'service_manager' => [
        'factories' => [
            'Thorr\OAuth\Options\ModuleOptions' => 'Thorr\OAuth\Options\ModuleOptionsFactory',
            'Thorr\OAuth\Adapter\Adapter' => 'Thorr\OAuth\Adapter\AdapterFactory',
            'Thorr\OAuth\Repository\UserRepository' => 'Thorr\OAuth\Repository\Doctrine\UserRepositoryFactory',
        ],
    ],

    'router' => [
        'routes' => [
            'oauth' => [
                'options' => [
                    'route' => '/oauth',
                ],
            ],
        ],
    ],

    'thorr_oauth' => [
//        'user_class_name' => 'Thorr\OAuth\Entity\User'
    ],

    'zf-oauth2' => [
        'storage' => 'Thorr\\OAuth\\Adapter\\Adapter',
    ],

    'doctrine' => [
        'driver' => [
            'Thorr\OAuth' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
                'paths' => __DIR__ . '/mappings',
            ],
            'orm_default' =>[
                'drivers' => [
                    'Thorr\OAuth\Entity' => 'Thorr\OAuth',
                ]
            ]
        ]
    ],

];

<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

return [

    'thorr_oauth' => [
//        'user_class_name' => 'Thorr\OAuth\Entity\User'
//        'bcrypt_cost' => 10,
//        'default_user_mapping_enabled' => true,
    ],

    'service_manager' => [
        'factories' => [
            'Thorr\OAuth\Options\ModuleOptions'     => 'Thorr\OAuth\Options\ModuleOptionsFactory',
            'Thorr\OAuth\Storage\DataMapperAdapter' => 'Thorr\OAuth\Storage\DataMapperAdapterFactory',
            'Thorr\OAuth\Password\Bcrypt'           => 'Thorr\OAuth\Password\BcryptFactory',
        ],
        'delegators' => [
            'ZF\OAuth2\Service\OAuth2Server' => [
                'Thorr\OAuth\Server\ServerDecorator',
            ],
        ],
    ],

    'repository_manager' => [
        'factories' => [
            'Thorr\OAuth\Repository\UserRepository' => 'Thorr\OAuth\Doctrine\Repository\UserRepositoryFactory',
        ],
        'repositories' => [
            'Thorr\OAuth\Entity\AccessToken'       => 'Thorr\OAuth\Repository\AccessTokenRepository',
            'Thorr\OAuth\Entity\AuthorizationCode' => 'Thorr\OAuth\Repository\AuthorizationCodeRepository',
            'Thorr\OAuth\Entity\Client'            => 'Thorr\OAuth\Repository\ClientRepository',
            'Thorr\OAuth\Entity\RefreshToken'      => 'Thorr\OAuth\Repository\RefreshTokenRepository',
            'Thorr\OAuth\Entity\Scope'             => 'Thorr\OAuth\Repository\ScopeRepository',
        ],
    ],

    'router' => [
        'routes' => [
            'oauth' => [
                'options' => [
                    'route' => '/api/oauth',
                ],
            ],
        ],
    ],

    'zf-oauth2' => [
        'storage' => 'Thorr\OAuth\Storage\DataMapperAdapter',
    ],

    'doctrine' => [
        'driver' => [
            'Thorr\OAuth' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
                'paths' => __DIR__ . '/mappings',
            ],
            'orm_default' =>[
                'drivers' => [
                    'Thorr\OAuth\Entity\AbstractToken'     => 'Thorr\OAuth',
                    'Thorr\OAuth\Entity\AccessToken'       => 'Thorr\OAuth',
                    'Thorr\OAuth\Entity\AuthorizationCode' => 'Thorr\OAuth',
                    'Thorr\OAuth\Entity\Client'            => 'Thorr\OAuth',
                    'Thorr\OAuth\Entity\RefreshToken'      => 'Thorr\OAuth',
                    'Thorr\OAuth\Entity\Scope'             => 'Thorr\OAuth',
                ]
            ]
        ]
    ],

];

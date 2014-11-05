<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

return [

    'thorr_oauth' => [
//        'user_entity_class_name' => 'Thorr\OAuth22\Entity\User',
//        'bcrypt_cost' => 10,
//        'default_user_mapping_enabled' => true,
//        'third_party_grant_type_enabled' => false,
        'third_party_providers' => [
            'facebook' => [
                'class' => 'Thorr\OAuth22\GrantType\ThirdParty\Provider\FacebookProvider',
                'options' => [
                    'app_id' => null,
                    'uri' => 'https://graph.facebook.com/v2.0',
                    'endpoint_params' => [],
                ],
            ],
            'instagram' => [
                'class' => 'Thorr\OAuth2\GrantType\ThirdParty\Provider\InstagramProvider',
                'options' => [
                    'client_id' => null,
                    'uri' => 'https://api.instagram.com/v1'
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            'Thorr\OAuth2\Options\ModuleOptions'     => 'Thorr\OAuth2\Options\ModuleOptionsFactory',
            'Thorr\OAuth2\Storage\DataMapperAdapter' => 'Thorr\OAuth2\Storage\DataMapperAdapterFactory',
            'Thorr\OAuth2\Password\Bcrypt'           => 'Thorr\OAuth2\Password\BcryptFactory',
            'Thorr\OAuth2\GrantType\ThirdParty'      => 'Thorr\OAuth2\GrantType\ThirdParty\ServiceFactory',
        ],
        'delegators' => [
            'ZF\OAuth2\Service\OAuth2Server' => [
                'Thorr\OAuth2\Server\ServerDecorator',
            ],
        ],
    ],

    'repository_manager' => [
        'factories' => [
            'Thorr\OAuth2\Repository\UserRepository' => 'Thorr\OAuth2\Doctrine\Repository\UserRepositoryFactory',
        ],
        'repositories' => [
            'Thorr\OAuth2\Entity\AccessToken'       => 'Thorr\OAuth2\Repository\AccessTokenRepository',
            'Thorr\OAuth2\Entity\AuthorizationCode' => 'Thorr\OAuth2\Repository\AuthorizationCodeRepository',
            'Thorr\OAuth2\Entity\Client'            => 'Thorr\OAuth2\Repository\ClientRepository',
            'Thorr\OAuth2\Entity\RefreshToken'      => 'Thorr\OAuth2\Repository\RefreshTokenRepository',
            'Thorr\OAuth2\Entity\Scope'             => 'Thorr\OAuth2\Repository\ScopeRepository',
            'Thorr\OAuth2\Entity\ThirdPartyUser'    => 'Thorr\OAuth2\Repository\ThirdPartyUserRepository',
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
        'storage' => 'Thorr\OAuth2\Storage\DataMapperAdapter',
    ],

];

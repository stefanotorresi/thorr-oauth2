<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2;

use Zend\ModuleManager\Feature;

class Module implements Feature\ConfigProviderInterface
{
    const DEFAULT_PASSWORD_SERVICE = 'Thorr\OAuth2\DefaultPasswordInterface';

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'thorr_oauth' => [
                //        'user_entity_class_name' => Entity\User::class,
                //        'bcrypt_cost' => 10,
                //        'default_user_mapping_enabled' => true,
                //        'third_party_grant_type_enabled' => false,
                'third_party_providers' => [
                    'facebook' => [
                        'class'   => GrantType\ThirdParty\Provider\FacebookProvider::class,
                        'options' => [
                            'app_id'          => null,
                            'uri'             => 'https://graph.facebook.com/v2.0',
                            'endpoint_params' => [],
                        ],
                    ],
                    'instagram' => [
                        'class'   => GrantType\ThirdParty\Provider\InstagramProvider::class,
                        'options' => [
                            'client_id' => null,
                            'uri'       => 'https://api.instagram.com/v1',
                        ],
                    ],
                ],
            ],

            'service_manager' => [
                'factories' => [
                    Options\ModuleOptions::class         => Factory\ModuleOptionsFactory::class,
                    Storage\DataMapperAdapter::class     => Factory\DataMapperAdapterFactory::class,
                    GrantType\ThirdPartyGrantType::class => Factory\ThirdPartyGrantTypeFactory::class,
                    static::DEFAULT_PASSWORD_SERVICE     => Factory\BcryptFactory::class,
                ],
                'delegators' => [
                    'ZF\OAuth2\Service\OAuth2Server' => [
                        Server\ServerInitializer::class,
                    ],
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
                'storage' => Storage\DataMapperAdapter::class,
            ],
        ];
    }
}

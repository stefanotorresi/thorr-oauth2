<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\GrantType\ThirdParty\Provider;

abstract class ProviderFactory
{
    public static function createProvider($spec)
    {
        $providerClass = is_string($spec) ? $spec
            : ((is_array($spec) && isset($spec['class'])) ? $spec['class'] : '');

        if (! class_exists($providerClass)) {
            throw new Exception\DomainException(sprintf(
                '%s expects the "class" attribute to resolve to an existing class; received "%s"',
                __METHOD__,
                $providerClass
            ));
        }

        $providerOptions = isset($spec['options']) ? $spec['options'] : [];

        $provider = new $providerClass($providerOptions);

        if (! $provider instanceof ProviderInterface) {
            throw new Exception\DomainException(sprintf(
                '%s expects the "class" attribute to resolve to a valid %s instance; received "%s"',
                __METHOD__,
                'Thorr\OAuth2\GrantType\ThirdParty\Provider\ProviderInterface',
                get_class($provider)
            ));
        }

        return $provider;
    }
}

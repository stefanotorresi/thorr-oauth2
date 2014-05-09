<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\GrantType\ThirdParty\Provider;

abstract class AbstractProvider implements ProviderInterface
{
    use ProviderTrait;

    public function __construct($options)
    {
        $this->setOptions($options);
    }
}

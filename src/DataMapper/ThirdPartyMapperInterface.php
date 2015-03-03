<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\DataMapper;

use Thorr\OAuth2\Entity\ThirdParty;
use Thorr\OAuth2\GrantType\ThirdParty\Provider\ProviderInterface;
use Thorr\Persistence\DataMapper\DataMapperInterface;

interface ThirdPartyMapperInterface extends DataMapperInterface
{
    /**
     * @param ProviderInterface $provider
     *
     * @return ThirdParty|null
     */
    public function findByProvider(ProviderInterface $provider);
}

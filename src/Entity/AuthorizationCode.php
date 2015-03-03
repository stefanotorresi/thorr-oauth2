<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

use Rhumsaa\Uuid\Uuid;

class AuthorizationCode extends AbstractToken
{
    use RedirectUriProviderTrait;

    /**
     * {@inheritdoc}
     *
     * @param string $redirectUri
     */
    public function __construct($uuid = null, $token, Client $client, UserInterface $user = null, $scopes = null, $redirectUri)
    {
        parent::__construct($uuid, $token, $client, $user, $scopes);

        $this->setRedirectUri($redirectUri);
    }
}

<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\GrantType;

use OAuth2\GrantType\GrantTypeInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\ResponseType\AccessTokenInterface;

class ThirdParty implements GrantTypeInterface
{
    public function getQuerystringIdentifier()
    {
        return 'third_party';
    }

    public function validateRequest(RequestInterface $request, ResponseInterface $response)
    {
        $response->setError(400, 'invalid_grant', 'Third party authentication failed');

        return null;
    }

    public function getClientId()
    {
        // TODO: Implement getClientId() method.
    }

    public function getUserId()
    {
        // TODO: Implement getUserId() method.
    }

    public function getScope()
    {
        // TODO: Implement getScope() method.
    }

    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        // TODO: Implement createAccessToken() method.
    }
}

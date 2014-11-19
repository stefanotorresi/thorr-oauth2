<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Test\Storage;

use DateTime;
use DomainException;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Thorr\OAuth2\Entity;
use Thorr\OAuth2\DataMapper;
use Thorr\OAuth2\Storage\DataMapperAdapter;
use Thorr\OAuth2\Test\Asset\ScopeAwareUser;
use Thorr\Persistence\DataMapper\DataMapperInterface;
use Thorr\Persistence\DataMapper\Manager\DataMapperManager;
use Zend\Crypt\Password\PasswordInterface;
use Zend\Math\Rand;

/**
 * @covers Thorr\OAuth2\Storage\DataMapperAdapter
 */
class DataMapperAdapterTest extends TestCase
{
    /**
     * @var DataMapperManager|MockObject
     */
    protected $dataMapperManager;

    /**
     * @var PasswordInterface|MockObject
     */
    protected $password;

    /**
     * @var array
     */
    protected $dataMapperMocks = [];

    /**
     *
     */
    protected function setUp()
    {
        $this->dataMapperManager = $this->getMock(DataMapperManager::class);
        $this->password          = $this->getMock(PasswordInterface::class);

        $this->dataMapperManager->expects($this->any())
            ->method('getDataMapperForEntity')
            ->willReturnCallback(function ($entityClassName) {
                if (! isset($this->dataMapperMocks[$entityClassName])) {
                    throw new DomainException(sprintf(
                        "Missing DataMapper mock for entity '%s'.\n".
                        "You can add it to the DataMapperManager mock via '%s::setDataMapperMock()'",
                        $entityClassName,
                        __CLASS__
                    ));
                }
                return $this->dataMapperMocks[$entityClassName];
            });
    }

    public function testConstructor()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);

        $this->assertSame($this->dataMapperManager, $dataMapperAdapter->getDataMapperManager());
        $this->assertSame($this->password, $dataMapperAdapter->getPassword());
    }

    public function testGetAccessToken()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $client            = new Entity\Client('someId');
        $user              = new Entity\User('someUser');
        $token             = Rand::getString(32);
        $accessToken       = new Entity\AccessToken($token, $client, $user);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn($accessToken);

        $this->setDataMapperMock(Entity\AccessToken::class, $tokenDataMapper);

        $tokenArray = $dataMapperAdapter->getAccessToken($token);

        $this->assertInternalType('array', $tokenArray);
        $this->assertEquals($accessToken->getExpiryUTCTimestamp(), $tokenArray['expires']);
        $this->assertEquals($accessToken->getClient()->getId(), $tokenArray['client_id']);
        $this->assertEquals($accessToken->getUser()->getId(), $tokenArray['user_id']);
        $this->assertEquals($accessToken->getScopesString(), $tokenArray['scope']);
    }

    public function testGetAccessTokenWithNullUser()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $client            = new Entity\Client('someId');
        $token             = Rand::getString(32);
        $accessToken       = new Entity\AccessToken($token, $client);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn($accessToken);

        $this->setDataMapperMock(Entity\AccessToken::class, $tokenDataMapper);

        $tokenArray = $dataMapperAdapter->getAccessToken($token);

        $this->assertInternalType('array', $tokenArray);
        $this->assertEquals($accessToken->getExpiryUTCTimestamp(), $tokenArray['expires']);
        $this->assertEquals($accessToken->getClient()->getId(), $tokenArray['client_id']);
        $this->assertNull($tokenArray['user_id']);
        $this->assertEquals($accessToken->getScopesString(), $tokenArray['scope']);
    }

    public function testGetAccessTokenWithInvalidToken()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $token             = Rand::getString(32);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn(null);

        $this->setDataMapperMock(Entity\AccessToken::class, $tokenDataMapper);

        $tokenArray = $dataMapperAdapter->getAccessToken($token);

        $this->assertNull($tokenArray);
    }

    public function testSetAccessToken()
    {
        $dataMapperAdapter  = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $token              = Rand::getString(32);
        $client             = new Entity\Client('someClient');
        $user               = new Entity\User('someUser');
        $expiryUTCTimestamp = time() + 1000;
        $scopeNames         = ['someScope', 'someOtherScope'];
        $scopeString        = implode(' ', $scopeNames);
        $scopes             = [new Entity\Scope($scopeNames[0]), new Entity\Scope($scopeNames[1])];

        $clientDataMapper = $this->getMock(DataMapperInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findById')
            ->with($client->getId())
            ->willReturn($client);

        $userDataMapper = $this->getMock(DataMapper\UserMapperInterface::class);
        $userDataMapper->expects($this->any())
            ->method('findById')
            ->with($user->getId())
            ->willReturn($user);

        $scopeDataMapper = $this->getMock(DataMapper\ScopeMapperInterface::class);
        $scopeDataMapper->expects($this->any())
            ->method('findScopes')
            ->with($scopeNames)
            ->willReturn($scopes);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->atLeastOnce())
            ->method('save')
            ->with($this->callback(function ($accessToken) use ($token, $client, $user, $expiryUTCTimestamp, $scopeString) {
                /** @var Entity\AccessToken $accessToken */
                $this->assertInstanceOf(Entity\AccessToken::class, $accessToken);
                $this->assertEquals($token, $accessToken->getToken());
                $this->assertSame($client, $accessToken->getClient());
                $this->assertSame($user, $accessToken->getUser());
                $this->assertSame($expiryUTCTimestamp, $accessToken->getExpiryUTCTimestamp());
                $this->assertCount(2, $accessToken->getScopes());
                $this->assertEquals($scopeString, $accessToken->getScopesString());

                return true;
            }));

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);
        $this->setDataMapperMock(Entity\UserInterface::class, $userDataMapper);
        $this->setDataMapperMock(Entity\Scope::class, $scopeDataMapper);
        $this->setDataMapperMock(Entity\AccessToken::class, $tokenDataMapper);

        $dataMapperAdapter->setAccessToken($token, $client->getId(), $user->getId(), $expiryUTCTimestamp, $scopeString);
    }

    public function testSetAccessTokenWithExistingToken()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $token             = Rand::getString(32);
        $client            = new Entity\Client('someClient');
        $newClient         = new Entity\Client('someOtherClient');
        $accessToken       = new Entity\AccessToken($token, $client);

        $clientDataMapper = $this->getMock(DataMapperInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findById')
            ->with($newClient->getId())
            ->willReturn($newClient);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn($accessToken);

        $tokenDataMapper->expects($this->atLeastOnce())
            ->method('save')
            ->with($accessToken);

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);
        $this->setDataMapperMock(Entity\AccessToken::class, $tokenDataMapper);

        $dataMapperAdapter->setAccessToken($token, $newClient->getId(), null, null, null);

        $this->assertSame($newClient, $accessToken->getClient());
    }

    public function testGetAuthorizationCode()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $client            = new Entity\Client('someId');
        $user              = new Entity\User('someUser');
        $token             = Rand::getString(32);
        $authCode          = new Entity\AuthorizationCode($token, $client, $user);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn($authCode);

        $this->setDataMapperMock(Entity\AuthorizationCode::class, $tokenDataMapper);

        $codeArray = $dataMapperAdapter->getAuthorizationCode($token);

        $this->assertInternalType('array', $codeArray);
        $this->assertEquals($authCode->getExpiryUTCTimestamp(), $codeArray['expires']);
        $this->assertEquals($authCode->getClient()->getId(), $codeArray['client_id']);
        $this->assertEquals($authCode->getUser()->getId(), $codeArray['user_id']);
        $this->assertEquals($authCode->getScopesString(), $codeArray['scope']);
        $this->assertEquals($authCode->getRedirectUri(), $codeArray['redirect_uri']);
    }

    public function testGetAuthorizationCodeWithInvalidToken()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $token             = Rand::getString(32);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn(null);

        $this->setDataMapperMock(Entity\AuthorizationCode::class, $tokenDataMapper);

        $tokenArray = $dataMapperAdapter->getAuthorizationCode($token);

        $this->assertNull($tokenArray);
    }


    public function testSetAuthorizationCode()
    {
        $dataMapperAdapter  = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $token              = Rand::getString(32);
        $client             = new Entity\Client('someClient');
        $user               = new Entity\User('someUser');
        $expiryUTCTimestamp = time() + 1000;
        $redirectUri        = 'someUri';
        $scopeNames         = ['someScope', 'someOtherScope'];
        $scopeString        = implode(' ', $scopeNames);
        $scopes             = [new Entity\Scope($scopeNames[0]), new Entity\Scope($scopeNames[1])];

        $clientDataMapper = $this->getMock(DataMapperInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findById')
            ->with($client->getId())
            ->willReturn($client);

        $userDataMapper = $this->getMock(DataMapper\UserMapperInterface::class);
        $userDataMapper->expects($this->any())
            ->method('findById')
            ->with($user->getId())
            ->willReturn($user);

        $scopeDataMapper = $this->getMock(DataMapper\ScopeMapperInterface::class);
        $scopeDataMapper->expects($this->any())
            ->method('findScopes')
            ->with($scopeNames)
            ->willReturn($scopes);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->atLeastOnce())
            ->method('save')
            ->with($this->callback(function ($authCode) use ($token, $client, $user, $redirectUri, $expiryUTCTimestamp, $scopeString) {
                /** @var Entity\AuthorizationCode $authCode */
                $this->assertInstanceOf(Entity\AuthorizationCode::class, $authCode);
                $this->assertEquals($token, $authCode->getToken());
                $this->assertSame($client, $authCode->getClient());
                $this->assertSame($user, $authCode->getUser());
                $this->assertSame($expiryUTCTimestamp, $authCode->getExpiryUTCTimestamp());
                $this->assertCount(2, $authCode->getScopes());
                $this->assertEquals($redirectUri, $authCode->getRedirectUri());
                $this->assertEquals($scopeString, $authCode->getScopesString());

                return true;
            }));

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);
        $this->setDataMapperMock(Entity\UserInterface::class, $userDataMapper);
        $this->setDataMapperMock(Entity\Scope::class, $scopeDataMapper);
        $this->setDataMapperMock(Entity\AuthorizationCode::class, $tokenDataMapper);

        $dataMapperAdapter->setAuthorizationCode(
            $token,
            $client->getId(),
            $user->getId(),
            $redirectUri,
            $expiryUTCTimestamp,
            $scopeString
        );
    }


    public function testSetAuthorizationCodeWithExistingToken()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $token             = Rand::getString(32);
        $client            = new Entity\Client('someClient');
        $user              = new Entity\User('someUser');
        $newClient         = new Entity\Client('someOtherClient');
        $authCode          = new Entity\AuthorizationCode($token, $client);

        $clientDataMapper = $this->getMock(DataMapperInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findById')
            ->with($newClient->getId())
            ->willReturn($newClient);

        $userDataMapper = $this->getMock(DataMapper\UserMapperInterface::class);
        $userDataMapper->expects($this->any())
            ->method('findById')
            ->with($user->getId())
            ->willReturn($user);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn($authCode);

        $tokenDataMapper->expects($this->atLeastOnce())
            ->method('save')
            ->with($authCode);

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);
        $this->setDataMapperMock(Entity\UserInterface::class, $userDataMapper);
        $this->setDataMapperMock(Entity\AuthorizationCode::class, $tokenDataMapper);

        $dataMapperAdapter->setAuthorizationCode($token, $newClient->getId(), $user->getId(), null, null);

        $this->assertSame($newClient, $authCode->getClient());
    }

    public function testExpireAuthorizationCode()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $token             = Rand::getString(32);
        $client            = new Entity\Client('someClient');
        $authCode          = new Entity\AuthorizationCode($token, $client);
        $expiryDate        = new DateTime('@'.(time() + 1000));
        $authCode->setExpiryDate($expiryDate);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn($authCode);

        $tokenDataMapper->expects($this->atLeastOnce())
            ->method('save')
            ->with($authCode);

        $this->setDataMapperMock(Entity\AuthorizationCode::class, $tokenDataMapper);

        $dataMapperAdapter->expireAuthorizationCode($token);
        $this->assertTrue($authCode->getExpiryDate() <= new DateTime());
    }

    /**
     * @param Entity\Client $client
     * @param string $secretToCheck
     * @param bool $expectedResult
     *
     * @dataProvider checkClientCredentialsProvider
     */
    public function testCheckClientCredentials($client, $secretToCheck, $expectedResult)
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);

        $clientDataMapper = $this->getMock(DataMapperInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findById')
            ->with($this->callback(function ($arg) use ($client) {
                return $client ? $client->getId() === $arg : $arg === 'invalid';
            }))
            ->willReturn($client);

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);

        $this->password->expects($this->any())
            ->method('verify')
            ->willReturnCallback(function () use ($client, $secretToCheck) {
                return $client->getSecret() === $secretToCheck;
            })
        ;

        $result = $dataMapperAdapter->checkClientCredentials($client ? $client->getId() : 'invalid', $secretToCheck);
        $this->assertSame($result, $expectedResult);
    }

    public function checkClientCredentialsProvider()
    {
        return [
            //  $client                                             $secretToCheck  $expectedResult
            [   new Entity\Client('someClient', 'clientSecret'),    'clientSecret', true    ],
            [   new Entity\Client('someClient', 'clientSecret'),    'bogus',        false   ],
            [   null,                                               null,           false   ]
        ];
    }

    /**
     * @param Entity\Client $client
     * @param bool $expectedResult
     *
     * @dataProvider isPublicClientProvider
     */
    public function testIsPublicClient($client, $expectedResult)
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);

        $clientDataMapper = $this->getMock(DataMapperInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findById')
            ->with($this->callback(function ($arg) use ($client) {
                return $client ? $client->getId() === $arg : $arg === 'invalid';
            }))
            ->willReturn($client);

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);

        $result = $dataMapperAdapter->isPublicClient($client ? $client->getId() : 'invalid');
        $this->assertSame($result, $expectedResult);
    }

    public function isPublicClientProvider()
    {
        return [
            //  $client                                             $expectedResult
            [   new Entity\Client('someClient', 'clientSecret'),    false   ],
            [   new Entity\Client('someClient'),                    true    ],
            [   null,                                               false   ]
        ];
    }

    public function testGetClientDetails()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $user              = new Entity\User('someUser');
        $client            = new Entity\Client('someClient', null, $user, ['foo', 'bar'], 'uri');

        $clientDataMapper = $this->getMock(DataMapperInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findById')
            ->with($client->getId())
            ->willReturn($client);

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);

        $clientArray = $dataMapperAdapter->getClientDetails($client->getId());

        $this->assertInternalType('array', $clientArray);
        $this->assertEquals($client->getRedirectUri(), $clientArray['redirect_uri']);
        $this->assertEquals($client->getId(), $clientArray['client_id']);
        $this->assertEquals($client->getGrantTypes(), $clientArray['grant_types']);
        $this->assertEquals($user->getId(), $clientArray['user_id']);
        $this->assertEquals($client->getScopesString(), $clientArray['scope']);
    }

    public function testGetClientDetailsWithInvalidId()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $bogusClientId     = 'invalid';

        $clientDataMapper = $this->getMock(DataMapperInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findById')
            ->with($bogusClientId)
            ->willReturn(null);

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);

        $this->assertFalse($dataMapperAdapter->getClientDetails($bogusClientId));
    }

    public function testGetClientScope()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $client            = new Entity\Client('someClient');
        $scopes            = [new Entity\Scope('someScope'), new Entity\Scope('someOtherScope')];
        $client->setScopes($scopes);

        $clientDataMapper = $this->getMock(DataMapperInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findById')
            ->with($client->getId())
            ->willReturn($client);

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);

        $this->assertEquals($client->getScopesString(), $dataMapperAdapter->getClientScope($client->getId()));
    }

    public function testGetClientScopeWithInvalidId()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $bogusClientId     = 'invalid';

        $clientDataMapper = $this->getMock(DataMapperInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findById')
            ->with($bogusClientId)
            ->willReturn(null);

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);

        $this->setExpectedException(InvalidArgumentException::class, 'Invalid clientId');
        $dataMapperAdapter->getClientScope($bogusClientId);
    }

    /**
     * @param Entity\Client $client
     * @param string $grantType
     * @param bool $expectedResult
     *
     * @dataProvider checkRestrictedGrantTypeProvider
     */
    public function testCheckRestrictedGrantType($client, $grantType, $expectedResult)
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $clientId          = $client ? $client->getId() : 'invalid';

        $clientDataMapper = $this->getMock(DataMapperInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findById')
            ->with($clientId)
            ->willReturn($client);

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);

        $result = $dataMapperAdapter->checkRestrictedGrantType($clientId, $grantType);

        $this->assertSame($expectedResult, $result);
    }

    public function checkRestrictedGrantTypeProvider()
    {
        return [
            //  $client                                                         $grantType  $expectedResult
            [   new Entity\Client('someClient', null, null, ['foo', 'bar']),    'foo',      true,   ],
            [   new Entity\Client('someClient', null, null, ['foo', 'bar']),    'bar',      true,   ],
            [   new Entity\Client('someClient', null, null, ['foo', 'bar']),    'baz',      false,  ],
            [   null,                                                           'bogus',    false,  ],
            [   new Entity\Client('someClient', null, null),                    'anything', true,   ],
        ];
    }

    public function testGetRefreshToken()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $client            = new Entity\Client('someId');
        $user              = new Entity\User('someUser');
        $token             = Rand::getString(32);
        $refreshToken      = new Entity\RefreshToken($token, $client, $user);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn($refreshToken);

        $this->setDataMapperMock(Entity\RefreshToken::class, $tokenDataMapper);

        $tokenArray = $dataMapperAdapter->getRefreshToken($token);

        $this->assertInternalType('array', $tokenArray);
        $this->assertEquals($refreshToken->getToken(), $tokenArray['refresh_token']);
        $this->assertEquals($refreshToken->getExpiryUTCTimestamp(), $tokenArray['expires']);
        $this->assertEquals($refreshToken->getClient()->getId(), $tokenArray['client_id']);
        $this->assertEquals($refreshToken->getUser()->getId(), $tokenArray['user_id']);
        $this->assertEquals($refreshToken->getScopesString(), $tokenArray['scope']);
    }

    public function testGetRefreshTokenWithNullUser()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $client            = new Entity\Client('someId');
        $token             = Rand::getString(32);
        $refreshToken      = new Entity\RefreshToken($token, $client);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn($refreshToken);

        $this->setDataMapperMock(Entity\RefreshToken::class, $tokenDataMapper);

        $tokenArray = $dataMapperAdapter->getRefreshToken($token);

        $this->assertInternalType('array', $tokenArray);
        $this->assertNull($tokenArray['user_id']);
    }

    public function testGetRefreshTokenWithInvalidToken()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $token             = Rand::getString(32);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn(null);

        $this->setDataMapperMock(Entity\RefreshToken::class, $tokenDataMapper);

        $tokenArray = $dataMapperAdapter->getRefreshToken($token);

        $this->assertNull($tokenArray);
    }

    public function testSetRefreshToken()
    {
        $dataMapperAdapter  = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $token              = Rand::getString(32);
        $client             = new Entity\Client('someClient');
        $user               = new Entity\User('someUser');
        $expiryUTCTimestamp = time() + 1000;
        $scopeNames         = ['someScope', 'someOtherScope'];
        $scopeString        = implode(' ', $scopeNames);
        $scopes             = [new Entity\Scope($scopeNames[0]), new Entity\Scope($scopeNames[1])];

        $clientDataMapper = $this->getMock(DataMapperInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findById')
            ->with($client->getId())
            ->willReturn($client);

        $userDataMapper = $this->getMock(DataMapper\UserMapperInterface::class);
        $userDataMapper->expects($this->any())
            ->method('findById')
            ->with($user->getId())
            ->willReturn($user);

        $scopeDataMapper = $this->getMock(DataMapper\ScopeMapperInterface::class);
        $scopeDataMapper->expects($this->any())
            ->method('findScopes')
            ->with($scopeNames)
            ->willReturn($scopes);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->atLeastOnce())
            ->method('save')
            ->with($this->callback(function ($refreshToken) use ($token, $client, $user, $expiryUTCTimestamp, $scopeString) {
                /** @var Entity\RefreshToken $refreshToken */
                $this->assertInstanceOf(Entity\RefreshToken::class, $refreshToken);
                $this->assertEquals($token, $refreshToken->getToken());
                $this->assertSame($client, $refreshToken->getClient());
                $this->assertSame($user, $refreshToken->getUser());
                $this->assertSame($expiryUTCTimestamp, $refreshToken->getExpiryUTCTimestamp());
                $this->assertCount(2, $refreshToken->getScopes());
                $this->assertEquals($scopeString, $refreshToken->getScopesString());

                return true;
            }));

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);
        $this->setDataMapperMock(Entity\UserInterface::class, $userDataMapper);
        $this->setDataMapperMock(Entity\Scope::class, $scopeDataMapper);
        $this->setDataMapperMock(Entity\RefreshToken::class, $tokenDataMapper);

        $dataMapperAdapter->setRefreshToken($token, $client->getId(), $user->getId(), $expiryUTCTimestamp, $scopeString);
    }

    public function testSetRefreshTokenWithExistingToken()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $token             = Rand::getString(32);
        $client            = new Entity\Client('someClient');
        $newClient         = new Entity\Client('someOtherClient');
        $refreshToken      = new Entity\RefreshToken($token, $client);

        $clientDataMapper = $this->getMock(DataMapperInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findById')
            ->with($newClient->getId())
            ->willReturn($newClient);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn($refreshToken);

        $tokenDataMapper->expects($this->atLeastOnce())
            ->method('save')
            ->with($refreshToken);

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);
        $this->setDataMapperMock(Entity\RefreshToken::class, $tokenDataMapper);

        $dataMapperAdapter->setRefreshToken($token, $newClient->getId(), null, null, null);

        $this->assertSame($newClient, $refreshToken->getClient());
    }

    public function testUnsetRefreshToken()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $token             = Rand::getString(32);
        $client            = new Entity\Client('someClient');
        $refreshToken      = new Entity\RefreshToken($token, $client);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn($refreshToken);

        $tokenDataMapper->expects($this->atLeastOnce())
            ->method('remove')
            ->with($refreshToken);

        $this->setDataMapperMock(Entity\RefreshToken::class, $tokenDataMapper);

        $dataMapperAdapter->unsetRefreshToken($token);
    }

    public function testUnsetRefreshTokenWithInvalidToken()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $bogusToken        = 'invalid';

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($bogusToken)
            ->willReturn(null);

        $tokenDataMapper->expects($this->never())->method('remove');

        $this->setDataMapperMock(Entity\RefreshToken::class, $tokenDataMapper);
        $this->setExpectedException(InvalidArgumentException::class, 'Invalid token');

        $dataMapperAdapter->unsetRefreshToken($bogusToken);
    }

    /**
     * @param array $scopeNames
     * @param string $inputScopeString
     * @param bool $expectedResult
     *
     * @dataProvider scopeExistsProvider
     */
    public function testScopeExists($scopeNames, $inputScopeString, $expectedResult)
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $scopes            = [];

        foreach ($scopeNames as $name) {
            $scopes[] = new Entity\Scope($name);
        }

        $scopeDataMapper = $this->getMock(DataMapper\ScopeMapperInterface::class);
        $scopeDataMapper->expects($this->any())
            ->method('findScopes')
            ->with(explode(' ', $inputScopeString))
            ->willReturnCallback(function ($inputScopes) use ($scopes) {
                return array_filter($scopes, function (Entity\Scope $scope) use ($inputScopes) {
                    return in_array($scope, $inputScopes);
                });
            });

        $this->setDataMapperMock(Entity\Scope::class, $scopeDataMapper);

        $result = $dataMapperAdapter->scopeExists($inputScopeString);

        $this->assertSame($expectedResult, $result);
    }

    public function scopeExistsProvider()
    {
        return [
            //  $scopeNames         $inputScopeString   $expectedResult
            [   [ 'foo', 'bar'],    'foo',              true    ],
            [   [ 'foo', 'bar'],    'bar',              true    ],
            [   [ 'foo', 'bar'],    'baz',              false   ],
            [   [ 'foo', 'bar'],    'baz bar',          false   ],
            [   [ 'bar', 'bar'],    'baz bar',          false   ],
            [   [],                 'any',              false   ],
        ];
    }

    /**
     * @param $scopes
     * @param $expectedResult
     *
     * @dataProvider getDefaultScopeProvider
     */
    public function testGetDefaultScope($scopes, $expectedResult)
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);

        $scopeDataMapper = $this->getMock(DataMapper\ScopeMapperInterface::class);
        $scopeDataMapper->expects($this->any())
            ->method('findDefaultScopes')
            ->willReturnCallback(function() use ($scopes) {
                return array_filter($scopes, function(Entity\Scope $scope) {
                    return $scope->isDefault();
                });
            });

        $this->setDataMapperMock(Entity\Scope::class, $scopeDataMapper);

        $this->assertSame($expectedResult, $dataMapperAdapter->getDefaultScope());
    }

    public function getDefaultScopeProvider()
    {
        return [
            [
                // $scopes
                [
                    new Entity\Scope('foo', true),
                    new Entity\Scope('bar', true),
                    new Entity\Scope('baz', false),
                ],
                // $expected result
                "foo bar"
            ],
            [
                // $scopes
                [
                    new Entity\Scope('foo', true),
                    new Entity\Scope('bar', false),
                ],
                // $expected result
                "foo"
            ],
            [
                // $scopes
                [
                    new Entity\Scope('foo', true),
                ],
                // $expected result
                "foo"
            ],
            [
                // $scopes
                [
                    new Entity\Scope('foo', false),
                ],
                // $expected result
                null
            ],
            [
                // $scopes
                [],
                // $expected result
                null
            ],
        ];
    }

    /**
     * @param Entity\User|null $user
     * @param string $secretToCheck
     * @param bool $expectedResult
     *
     * @dataProvider checkUserCredentialsProvider
     */
    public function testCheckUserCredentials($user, $secretToCheck, $expectedResult)
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $userId            = $user ? $user->getId() : 'invalid';

        $userDataMapper = $this->getMock(DataMapper\UserMapperInterface::class);
        $userDataMapper->expects($this->any())
            ->method('findByCredential')
            ->with($userId)
            ->willReturn($user);

        $this->setDataMapperMock(Entity\UserInterface::class, $userDataMapper);

        $this->password->expects($this->any())
            ->method('verify')
            ->willReturnCallback(function () use ($user, $secretToCheck) {
                return $user->getPassword() === $secretToCheck;
            })
        ;

        $result = $dataMapperAdapter->checkUserCredentials($userId, $secretToCheck);
        $this->assertSame($result, $expectedResult);
    }

    public function checkUserCredentialsProvider()
    {
        return [
            //  $client                                     $secretToCheck  $expectedResult
            [   new Entity\User('someUser', 'password'),    'password',     true    ],
            [   new Entity\User('someUser', 'password'),    'bogus',        false   ],
            [   null,                                        null,          false   ]
        ];
    }

    /**
     * @param Entity\User|null $user
     * @param mixed $expectedResult
     *
     * @dataProvider getUserDetailsProvider
     */
    public function testGetUserDetails($user, $expectedResult)
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $userId            = $user ? $user->getId() : 'invalid';

        $userDataMapper = $this->getMock(DataMapper\UserMapperInterface::class);
        $userDataMapper->expects($this->any())
            ->method('findByCredential')
            ->with($userId)
            ->willReturn($user);

        $this->setDataMapperMock(Entity\UserInterface::class, $userDataMapper);

        $this->assertSame($expectedResult, $dataMapperAdapter->getUserDetails($userId));
    }

    public function getUserDetailsProvider()
    {
        $scopeAwareUser = new ScopeAwareUser('someUser');
        $scopeAwareUser->setScopes([new Entity\Scope('foo'), new Entity\Scope('bar')]);

        return [
            //  $user                           $expectedResult
            [   new Entity\User('someUser'),    [ 'user_id' => 'someUser', 'scope' => null ]    ],
            [   $scopeAwareUser,                [ 'user_id' => 'someUser', 'scope' => "foo bar" ]    ],
            [   null,                           false   ],
        ];
    }

    protected function setDataMapperMock($entityClassName, DataMapperInterface $dataMapper)
    {
        $this->dataMapperMocks[$entityClassName] = $dataMapper;
    }
}

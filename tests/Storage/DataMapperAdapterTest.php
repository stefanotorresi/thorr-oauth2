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
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use Thorr\OAuth2\DataMapper;
use Thorr\OAuth2\Entity;
use Thorr\OAuth2\GrantType\UserCredentials\PasswordStrategy;
use Thorr\OAuth2\GrantType\UserCredentials\UserCredentialsStrategyInterface;
use Thorr\OAuth2\Storage\DataMapperAdapter;
use Thorr\OAuth2\Test\Asset\ScopeAwareUser;
use Thorr\Persistence\DataMapper\DataMapperInterface;
use Thorr\Persistence\DataMapper\EntityFinderInterface;
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
                        "Missing DataMapper mock for entity '%s'.\n" .
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
        $client            = new Entity\Client();
        $user              = new Entity\User();
        $token             = Rand::getString(32);
        $accessToken       = new Entity\AccessToken(null, $token, $client, $user);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn($accessToken);

        $this->setDataMapperMock(Entity\AccessToken::class, $tokenDataMapper);

        $tokenArray = $dataMapperAdapter->getAccessToken($token);

        $this->assertInternalType('array', $tokenArray);
        $this->assertEquals($accessToken->getExpiryUTCTimestamp(), $tokenArray['expires']);
        $this->assertEquals($accessToken->getClient()->getUuid(), $tokenArray['client_id']);
        $this->assertEquals($accessToken->getUser()->getUuid(), $tokenArray['user_id']);
        $this->assertEquals($accessToken->getScopesString(), $tokenArray['scope']);
    }

    public function testGetAccessTokenWithNullUser()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $client            = new Entity\Client();
        $token             = Rand::getString(32);
        $accessToken       = new Entity\AccessToken(null, $token, $client);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn($accessToken);

        $this->setDataMapperMock(Entity\AccessToken::class, $tokenDataMapper);

        $tokenArray = $dataMapperAdapter->getAccessToken($token);

        $this->assertInternalType('array', $tokenArray);
        $this->assertEquals($accessToken->getExpiryUTCTimestamp(), $tokenArray['expires']);
        $this->assertEquals($accessToken->getClient()->getUuid(), $tokenArray['client_id']);
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
        $client             = new Entity\Client();
        $user               = new Entity\User();
        $expiryUTCTimestamp = time() + 1000;
        $scopeNames         = ['someScope', 'someOtherScope'];
        $scopeString        = implode(' ', $scopeNames);
        $scopes             = [new Entity\Scope(null, $scopeNames[0]), new Entity\Scope(null, $scopeNames[1])];

        $clientDataMapper = $this->getMock(EntityFinderInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findByUuid')
            ->with($client->getUuid())
            ->willReturn($client);

        $userDataMapper = $this->getMock(DataMapper\UserMapperInterface::class);
        $userDataMapper->expects($this->any())
            ->method('findByUuid')
            ->with($user->getUuid())
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
                /* @var Entity\AccessToken $accessToken */
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

        $dataMapperAdapter->setAccessToken($token, $client->getUuid(), $user->getUuid(), $expiryUTCTimestamp, $scopeString);
    }

    public function testSetAccessTokenWithExistingToken()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $token             = Rand::getString(32);
        $client            = new Entity\Client();
        $newClient         = new Entity\Client();
        $accessToken       = new Entity\AccessToken(null, $token, $client);

        $clientDataMapper = $this->getMock(EntityFinderInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findByUuid')
            ->with($newClient->getUuid())
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

        $dataMapperAdapter->setAccessToken($token, $newClient->getUuid(), null, null, null);

        $this->assertSame($newClient, $accessToken->getClient());
    }

    public function testGetAuthorizationCode()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $client            = new Entity\Client();
        $user              = new Entity\User();
        $token             = Rand::getString(32);
        $authCode          = new Entity\AuthorizationCode(null, $token, $client, $user, null, 'someUri');

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn($authCode);

        $this->setDataMapperMock(Entity\AuthorizationCode::class, $tokenDataMapper);

        $codeArray = $dataMapperAdapter->getAuthorizationCode($token);

        $this->assertInternalType('array', $codeArray);
        $this->assertEquals($authCode->getExpiryUTCTimestamp(), $codeArray['expires']);
        $this->assertEquals($authCode->getClient()->getUuid(), $codeArray['client_id']);
        $this->assertEquals($authCode->getUser()->getUuid(), $codeArray['user_id']);
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
        $client             = new Entity\Client();
        $user               = new Entity\User();
        $expiryUTCTimestamp = time() + 1000;
        $redirectUri        = 'someUri';
        $scopeNames         = ['someScope', 'someOtherScope'];
        $scopeString        = implode(' ', $scopeNames);
        $scopes             = [new Entity\Scope(null, $scopeNames[0]), new Entity\Scope(null, $scopeNames[1])];

        $clientDataMapper = $this->getMock(EntityFinderInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findByUuid')
            ->with($client->getUuid())
            ->willReturn($client);

        $userDataMapper = $this->getMock(DataMapper\UserMapperInterface::class);
        $userDataMapper->expects($this->any())
            ->method('findByUuid')
            ->with($user->getUuid())
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
                /* @var Entity\AuthorizationCode $authCode */
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
            $client->getUuid(),
            $user->getUuid(),
            $redirectUri,
            $expiryUTCTimestamp,
            $scopeString
        );
    }

    public function testSetAuthorizationCodeWithExistingToken()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $token             = Rand::getString(32);
        $client            = new Entity\Client();
        $user              = new Entity\User();
        $newClient         = new Entity\Client();
        $authCode          = new Entity\AuthorizationCode(null, $token, $client, $user, null, 'someUri');

        $clientDataMapper = $this->getMock(EntityFinderInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findByUuid')
            ->with($newClient->getUuid())
            ->willReturn($newClient);

        $userDataMapper = $this->getMock(DataMapper\UserMapperInterface::class);
        $userDataMapper->expects($this->any())
            ->method('findByUuid')
            ->with($user->getUuid())
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

        $dataMapperAdapter->setAuthorizationCode($token, $newClient->getUuid(), $user->getUuid(), null, null);

        $this->assertSame($newClient, $authCode->getClient());
    }

    public function testExpireAuthorizationCode()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $token             = Rand::getString(32);
        $client            = new Entity\Client();
        $authCode          = new Entity\AuthorizationCode(null, $token, $client, null, null, 'someUri');
        $expiryDate        = new DateTime('@' . (time() + 1000));
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
     * @param string        $secretToCheck
     * @param bool          $expectedResult
     *
     * @dataProvider checkClientCredentialsProvider
     */
    public function testCheckClientCredentials($client, $secretToCheck, $expectedResult)
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);

        $clientDataMapper = $this->getMock(EntityFinderInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findByUuid')
            ->with($this->callback(function ($arg) use ($client) {
                return $client ? $client->getUuid() === $arg : $arg === 'invalid';
            }))
            ->willReturn($client);

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);

        $this->password->expects($this->any())
            ->method('verify')
            ->willReturnCallback(function () use ($client, $secretToCheck) {
                return $client->getSecret() === $secretToCheck;
            })
        ;

        $result = $dataMapperAdapter->checkClientCredentials($client ? $client->getUuid() : 'invalid', $secretToCheck);
        $this->assertSame($result, $expectedResult);
    }

    public function checkClientCredentialsProvider()
    {
        return [
            //  $client                                     $secretToCheck  $expectedResult
            [new Entity\Client(null, 'clientSecret'),    'clientSecret', true],
            [new Entity\Client(null, 'clientSecret'),    'bogus',        false],
            [null,                                       null,           false],
        ];
    }

    /**
     * @param Entity\Client $client
     * @param bool          $expectedResult
     *
     * @dataProvider isPublicClientProvider
     */
    public function testIsPublicClient($client, $expectedResult)
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);

        $clientDataMapper = $this->getMock(EntityFinderInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findByUuid')
            ->with($this->callback(function ($arg) use ($client) {
                return $client ? $client->getUuid() === $arg : $arg === 'invalid';
            }))
            ->willReturn($client);

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);

        $result = $dataMapperAdapter->isPublicClient($client ? $client->getUuid() : 'invalid');
        $this->assertSame($result, $expectedResult);
    }

    public function isPublicClientProvider()
    {
        return [
            //  $client                                     $expectedResult
            [new Entity\Client(null, 'clientSecret'),    false],
            [new Entity\Client(),                        true],
            [null,                                       false],
        ];
    }

    /**
     * @param Entity\Client|null $client
     * @param mixed              $expectedResult
     *
     * @dataProvider getClientDetailsProvider
     */
    public function testGetClientDetails($client, $expectedResult)
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $clientUuid        = $client ? $client->getUuid() : 'invalid';

        $clientDataMapper = $this->getMock(EntityFinderInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findByUuid')
            ->with($clientUuid)
            ->willReturn($client);

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);

        $this->assertSame($expectedResult, $dataMapperAdapter->getClientDetails($clientUuid));
    }

    public function getClientDetailsProvider()
    {
        $client = new Entity\Client(null, null, new Entity\User(), ['foo', 'bar'], 'uri');

        return [
            [
                $client,
                [
                    'redirect_uri' => $client->getRedirectUri(),
                    'client_id'    => $client->getUuid(),
                    'grant_types'  => $client->getGrantTypes(),
                    'user_id'      => $client->getUser()->getUuid(),
                    'scope'        => $client->getScopesString(),
                ],
            ],
            [
                null, false,
            ],
        ];
    }

    public function testGetClientScope()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $client            = new Entity\Client();
        $scopes            = [new Entity\Scope(null, 'someScope'), new Entity\Scope(null, 'someOtherScope')];
        $client->setScopes($scopes);

        $clientDataMapper = $this->getMock(EntityFinderInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findByUuid')
            ->with($client->getUuid())
            ->willReturn($client);

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);

        $this->assertEquals($client->getScopesString(), $dataMapperAdapter->getClientScope($client->getUuid()));
    }

    public function testGetClientScopeWithInvalidId()
    {
        $dataMapperAdapter   = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $bogusClientUuid     = 'invalid';

        $clientDataMapper = $this->getMock(EntityFinderInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findByUuid')
            ->with($bogusClientUuid)
            ->willReturn(null);

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);

        $this->setExpectedException(InvalidArgumentException::class, 'Invalid client uuid');
        $dataMapperAdapter->getClientScope($bogusClientUuid);
    }

    /**
     * @param Entity\Client $client
     * @param string        $grantType
     * @param bool          $expectedResult
     *
     * @dataProvider checkRestrictedGrantTypeProvider
     */
    public function testCheckRestrictedGrantType($client, $grantType, $expectedResult)
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $clientUuid        = $client ? $client->getUuid() : 'invalid';

        $clientDataMapper = $this->getMock(EntityFinderInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findByUuid')
            ->with($clientUuid)
            ->willReturn($client);

        $this->setDataMapperMock(Entity\Client::class, $clientDataMapper);

        $result = $dataMapperAdapter->checkRestrictedGrantType($clientUuid, $grantType);

        $this->assertSame($expectedResult, $result);
    }

    public function checkRestrictedGrantTypeProvider()
    {
        return [
            //  $client                                                 $grantType  $expectedResult
            [new Entity\Client(null, null, null, ['foo', 'bar']),    'foo',      true,   ],
            [new Entity\Client(null, null, null, ['foo', 'bar']),    'bar',      true,   ],
            [new Entity\Client(null, null, null, ['foo', 'bar']),    'baz',      false,  ],
            [null,                                                   'bogus',    false,  ],
            [new Entity\Client(null, null, null),                    'anything', true,   ],
        ];
    }

    public function testGetRefreshToken()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $client            = new Entity\Client();
        $user              = new Entity\User();
        $token             = Rand::getString(32);
        $refreshToken      = new Entity\RefreshToken(null, $token, $client, $user);

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
        $this->assertEquals($refreshToken->getClient()->getUuid(), $tokenArray['client_id']);
        $this->assertEquals($refreshToken->getUser()->getUuid(), $tokenArray['user_id']);
        $this->assertEquals($refreshToken->getScopesString(), $tokenArray['scope']);
    }

    public function testGetRefreshTokenWithNullUser()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $client            = new Entity\Client();
        $token             = Rand::getString(32);
        $refreshToken      = new Entity\RefreshToken(null, $token, $client);

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
        $client             = new Entity\Client();
        $user               = new Entity\User();
        $expiryUTCTimestamp = time() + 1000;
        $scopeNames         = ['someScope', 'someOtherScope'];
        $scopeString        = implode(' ', $scopeNames);
        $scopes             = [new Entity\Scope(null, $scopeNames[0]), new Entity\Scope(null, $scopeNames[1])];

        $clientDataMapper = $this->getMock(EntityFinderInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findByUuid')
            ->with($client->getUuid())
            ->willReturn($client);

        $userDataMapper = $this->getMock(DataMapper\UserMapperInterface::class);
        $userDataMapper->expects($this->any())
            ->method('findByUuid')
            ->with($user->getUuid())
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
                /* @var Entity\RefreshToken $refreshToken */
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

        $dataMapperAdapter->setRefreshToken($token, $client->getUuid(), $user->getUuid(), $expiryUTCTimestamp, $scopeString);
    }

    public function testSetRefreshTokenWithExistingToken()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $token             = Rand::getString(32);
        $client            = new Entity\Client();
        $newClient         = new Entity\Client();
        $refreshToken      = new Entity\RefreshToken(null, $token, $client);

        $clientDataMapper = $this->getMock(EntityFinderInterface::class);
        $clientDataMapper->expects($this->any())
            ->method('findByUuid')
            ->with($newClient->getUuid())
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

        $dataMapperAdapter->setRefreshToken($token, $newClient->getUuid(), null, null, null);

        $this->assertSame($newClient, $refreshToken->getClient());
    }

    public function testUnsetRefreshToken()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $token             = Rand::getString(32);
        $client            = new Entity\Client();
        $refreshToken      = new Entity\RefreshToken(null, $token, $client);

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
     * @param array  $scopeNames
     * @param string $inputScopeString
     * @param bool   $expectedResult
     *
     * @dataProvider scopeExistsProvider
     */
    public function testScopeExists($scopeNames, $inputScopeString, $expectedResult)
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $scopes            = [];

        foreach ($scopeNames as $name) {
            $scopes[] = new Entity\Scope(null, $name);
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
            [['foo', 'bar'],    'foo',              true],
            [['foo', 'bar'],    'bar',              true],
            [['foo', 'bar'],    'baz',              false],
            [['foo', 'bar'],    'baz bar',          false],
            [['bar', 'bar'],    'baz bar',          false],
            [[],                 'any',              false],
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
            ->willReturnCallback(function () use ($scopes) {
                return array_filter($scopes, function (Entity\Scope $scope) {
                    return $scope->isDefaultScope();
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
                    new Entity\Scope(null, 'foo', true),
                    new Entity\Scope(null, 'bar', true),
                    new Entity\Scope(null, 'baz', false),
                ],
                // $expected result
                'foo bar',
            ],
            [
                // $scopes
                [
                    new Entity\Scope(null, 'foo', true),
                    new Entity\Scope(null, 'bar', false),
                ],
                // $expected result
                'foo',
            ],
            [
                // $scopes
                [
                    new Entity\Scope(null, 'foo', true),
                ],
                // $expected result
                'foo',
            ],
            [
                // $scopes
                [
                    new Entity\Scope(null, 'foo', false),
                ],
                // $expected result
                null,
            ],
            [
                // $scopes
                [],
                // $expected result
                null,
            ],
        ];
    }

    /**
     * @param Entity\User|null $user
     * @param string           $secretToCheck
     * @param bool             $expectedResult
     *
     * @dataProvider checkUserCredentialsProvider
     */
    public function testCheckUserCredentials($user, $secretToCheck, $expectedResult)
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $userUuid          = $user ? $user->getUuid() : 'invalid';

        $userDataMapper = $this->getMock(DataMapper\UserMapperInterface::class);
        $userDataMapper->expects($this->any())
            ->method('findByCredential')
            ->with($userUuid)
            ->willReturn($user);

        $this->setDataMapperMock(Entity\UserInterface::class, $userDataMapper);

        $this->password->expects($this->any())
            ->method('verify')
            ->willReturnCallback(function () use ($user, $secretToCheck) {
                return $user->getPassword() === $secretToCheck;
            })
        ;

        $result = $dataMapperAdapter->checkUserCredentials($userUuid, $secretToCheck);
        $this->assertSame($result, $expectedResult);
    }

    public function checkUserCredentialsProvider()
    {
        return [
            //  $client                               $secretToCheck    $expectedResult
            [new Entity\User(null, 'password'),    'password',       true],
            [new Entity\User(null, 'password'),    'bogus',          false],
            [null,                                 null,             false],
        ];
    }

    /**
     * @param Entity\User|null $user
     * @param mixed            $expectedResult
     *
     * @dataProvider getUserDetailsProvider
     */
    public function testGetUserDetails($user, $expectedResult)
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $userUuid          = $user ? $user->getUuid() : 'invalid';

        $userDataMapper = $this->getMock(DataMapper\UserMapperInterface::class);
        $userDataMapper->expects($this->any())
            ->method('findByCredential')
            ->with($userUuid)
            ->willReturn($user);

        $this->setDataMapperMock(Entity\UserInterface::class, $userDataMapper);

        $this->assertSame($expectedResult, $dataMapperAdapter->getUserDetails($userUuid));
    }

    public function getUserDetailsProvider()
    {
        $normalUser     = new Entity\User();
        $scopeAwareUser = new ScopeAwareUser();
        $scopeAwareUser->setScopes([new Entity\Scope(null, 'foo'), new Entity\Scope(null, 'bar')]);

        return [
            //  $user               $expectedResult
            [$normalUser,        ['user_id' => $normalUser->getUuid(), 'scope' => null]],
            [$scopeAwareUser,    ['user_id' => $scopeAwareUser->getUuid(), 'scope' => 'foo bar']],
            [null,               false],
        ];
    }

    public function testUserClassSetterChangesUserDataMapper()
    {
        $dataMapperManager  = $this->getMock(DataMapperManager::class);
        $userDataMapper     = $this->getMock(DataMapper\UserMapperInterface::class);

        $dataMapperAdapter  = new DataMapperAdapter($dataMapperManager, $this->password);
        $dataMapperAdapter->setUserClass(Entity\User::class);

        $dataMapperManager->expects($this->atLeastOnce())
            ->method('getDataMapperForEntity')
            ->with($dataMapperAdapter->getUserClass())
            ->willReturn($userDataMapper);

        $dataMapperAdapter->getUserDetails(null);
    }

    public function testUserClassSetterThrowsException()
    {
        $dataMapperManager  = $this->getMock(DataMapperManager::class);

        $dataMapperAdapter  = new DataMapperAdapter($dataMapperManager, $this->password);

        $this->setExpectedException(InvalidArgumentException::class, 'Invalid user class');
        $dataMapperAdapter->setUserClass('foo');

        $dataMapperAdapter  = new DataMapperAdapter($dataMapperManager, $this->password);

        $this->setExpectedException(InvalidArgumentException::class, 'Invalid user class');
        $dataMapperAdapter->setUserClass(\stdClass::class);
    }

    public function testHasDefaultUserCredentialsStrategy()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $strategies        = $dataMapperAdapter->getUserCredentialsStrategies();
        $this->assertNotEmpty($strategies);
        $this->assertArrayHasKey('default', $strategies);
        $this->assertInstanceOf(PasswordStrategy::class, $strategies['default']);
    }

    public function testCanDisableDefaultUserCredentialsStrategyViaConstructor()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password, false);
        $this->assertEmpty($dataMapperAdapter->getUserCredentialsStrategies());
    }

    /**
     * @param callable|UserCredentialsStrategyInterface $strategy
     * @param bool                                      $expectException
     *
     * @dataProvider strategyProvider
     */
    public function testAddUserCredentialsStrategy($strategy, $expectException = false)
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password, false);

        if ($expectException) {
            $this->setExpectedException(InvalidArgumentException::class, 'User credential strategy must be a callable or implement');
        }

        $dataMapperAdapter->addUserCredentialsStrategy($strategy, 'test');
        $this->assertContains($strategy, $dataMapperAdapter->getUserCredentialsStrategies());
    }

    public function strategyProvider()
    {
        return [
            [ function () {} ],
            [ $this->getMock(UserCredentialsStrategyInterface::class) ],
            [ new \stdClass(), true ],
        ];
    }

    public function testRemoveUserCredentialsStrategy()
    {
        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $dataMapperAdapter->removeUserCredentialsStrategy('default');
        $this->assertEmpty($dataMapperAdapter->getUserCredentialsStrategies());
    }

    public function testCheckUserCredentialsWillUseStrategyIfAvailable()
    {
        $dataMapperAdapter  = new DataMapperAdapter($this->dataMapperManager, $this->password, false);
        $userDataMapper     = $this->getMock(DataMapper\UserMapperInterface::class);
        $user               = new Entity\User();
        $testPassword       = 'foobar';
        $returnValue        = true;
        $userDataMapper->expects($this->any())
                       ->method('findByCredential')
                       ->willReturn($user);

        $this->setDataMapperMock(Entity\UserInterface::class, $userDataMapper);

        $userCredentialStrategy = $this->getMock(UserCredentialsStrategyInterface::class);
        $userCredentialStrategy
            ->expects($this->atLeastOnce())
            ->method('isValid')
            ->with($user, $testPassword)
            ->willReturn($returnValue)
        ;

        $dataMapperAdapter->addUserCredentialsStrategy($userCredentialStrategy, 'test');
        $result = $dataMapperAdapter->checkUserCredentials('foo', $testPassword);
        $this->assertSame($returnValue, $result);
    }

    public function testCheckUserCredentialsWillUseStrategyCallableIfAvailable()
    {
        $dataMapperAdapter  = new DataMapperAdapter($this->dataMapperManager, $this->password, false);
        $userDataMapper     = $this->getMock(DataMapper\UserMapperInterface::class);
        $user               = new Entity\User();
        $testPassword       = 'foobar';
        $returnValue        = true;
        $userDataMapper->expects($this->any())
                       ->method('findByCredential')
                       ->willReturn($user);

        $this->setDataMapperMock(Entity\UserInterface::class, $userDataMapper);

        $args                   = [];
        $userCredentialStrategy = function ($user, $password) use (&$args, $returnValue) {
            $args = func_get_args();

            return $returnValue;
        };

        $dataMapperAdapter->addUserCredentialsStrategy($userCredentialStrategy, 'test');
        $result = $dataMapperAdapter->checkUserCredentials('foo', $testPassword);
        $this->assertNotEmpty($args);
        $this->assertSame($args[0], $user);
        $this->assertSame($args[1], $testPassword);
        $this->assertSame($returnValue, $result);
    }

    public function testCheckUserCredentialsThrowsIfInvalidDataMapperIsUsed()
    {
        $dataMapper = $this->getMock(DataMapperInterface::class);
        $this->setDataMapperMock(Entity\UserInterface::class, $dataMapper);
        $this->setExpectedException(\Assert\InvalidArgumentException::class, 'was expected to be instanceof of "Thorr\OAuth2\DataMapper\UserMapperInterface" but is not.');

        $dataMapperAdapter = new DataMapperAdapter($this->dataMapperManager, $this->password);
        $dataMapperAdapter->checkUserCredentials('foo', 'bar');
    }

    protected function setDataMapperMock($entityClassName, DataMapperInterface $dataMapper)
    {
        $this->dataMapperMocks[$entityClassName] = $dataMapper;
    }
}

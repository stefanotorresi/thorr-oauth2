<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Test\Storage;

use DateTime;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Thorr\OAuth2\Entity;
use Thorr\OAuth2\DataMapper;
use Thorr\OAuth2\Storage\DataMapperAdapter;
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
     * @var PasswordInterface
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
            ->with($this->callback(function ($accessToken) use ($client, $user, $expiryUTCTimestamp, $scopeString) {
                /** @var Entity\AccessToken $accessToken */
                $this->assertInstanceOf(Entity\AccessToken::class, $accessToken);
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
            ->with($this->callback(function ($authCode) use ($client, $user, $redirectUri, $expiryUTCTimestamp, $scopeString) {
                /** @var Entity\AuthorizationCode $authCode */
                $this->assertInstanceOf(Entity\AuthorizationCode::class, $authCode);
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

    protected function setDataMapperMock($entityClassName, DataMapperInterface $dataMapper)
    {
        $this->dataMapperMocks[$entityClassName] = $dataMapper;
    }
}

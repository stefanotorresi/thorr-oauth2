<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Test\Storage;

use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Thorr\OAuth2\Entity;
use Thorr\OAuth2\DataMapper;
use Thorr\OAuth2\Storage\DataMapperAdapter;
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
     *
     */
    protected function setUp()
    {
        $this->dataMapperManager = $this->getMock(DataMapperManager::class);
        $this->password          = $this->getMock(PasswordInterface::class);
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
        $client = new Entity\Client('someId');
        $user = new Entity\User('someUser');
        $token = Rand::getString(32);
        $accessToken = new Entity\AccessToken($token, $client, $user);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn($accessToken);

        $this->dataMapperManager->expects($this->any())
            ->method('getDataMapperForEntity')
            ->with(Entity\AccessToken::class)
            ->willReturn($tokenDataMapper);

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
        $client = new Entity\Client('someId');
        $token = Rand::getString(32);
        $accessToken = new Entity\AccessToken($token, $client);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn($accessToken);

        $this->dataMapperManager->expects($this->any())
            ->method('getDataMapperForEntity')
            ->with(Entity\AccessToken::class)
            ->willReturn($tokenDataMapper);

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
        $token = Rand::getString(32);

        $tokenDataMapper = $this->getMock(DataMapper\TokenMapperInterface::class);
        $tokenDataMapper->expects($this->any())
            ->method('findByToken')
            ->with($token)
            ->willReturn(null);

        $this->dataMapperManager->expects($this->any())
            ->method('getDataMapperForEntity')
            ->with(Entity\AccessToken::class)
            ->willReturn($tokenDataMapper);

        $tokenArray = $dataMapperAdapter->getAccessToken($token);

        $this->assertNull($tokenArray);
    }
}

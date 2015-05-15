<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Test\Server;

use OAuth2\Server as OAuth2Server;
use PHPUnit_Framework_TestCase as TestCase;
use Thorr\OAuth2\Options\ModuleOptions;
use Thorr\OAuth2\Server\ServerInitializer;
use Zend\ServiceManager\ServiceLocatorInterface;

class ServerInitializerTest extends TestCase
{
    public function testInitializationWhenCallbackReturnsCallable()
    {
        $serviceManager = $this->getMock(ServiceLocatorInterface::class);
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($name) {
                switch ($name) {
                    case ModuleOptions::class :
                        return new ModuleOptions();
                }
            })
        ;
        $oauth2Server = $this->getMockBuilder(OAuth2Server::class)->disableOriginalConstructor()->getMock();
        $callback     = function () use ($oauth2Server) {
            return function () use ($oauth2Server) {
                return $oauth2Server;
            };
        };
        $initializer = new ServerInitializer();

        $result = $initializer->createDelegatorWithName($serviceManager, '', '', $callback);

        $this->assertEquals($result, $oauth2Server);
    }
}

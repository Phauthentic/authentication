<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\Test\TestCase\Authenticator\Storage;

use ArrayObject;
use Authentication\Authenticator\Storage\CakeSessionStorage;
use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\ResponseInterface;

class CakeSessionStorageTest extends TestCase
{

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $class = '\Cake\Http\Session';
        if (!class_exists($class)) {
            $class = '\Cake\Network\Session';
        }
        $this->sessionMock = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->setMethods(['read', 'write', 'delete'])
            ->getMock();
    }

    /**
     * testRead
     *
     * @return void
     */
    public function testRead()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/']);
        $response = new Response();

        $this->sessionMock->expects($this->at(0))
            ->method('read')
            ->with('Auth')
            ->will($this->returnValue([
                'username' => 'mariano',
                'password' => 'password'
            ]));

        $request = $request->withAttribute('session', $this->sessionMock);

        $storage = new CakeSessionStorage();
        $result = $storage->read($request, $response);
        $this->assertEquals([
            'username' => 'mariano',
            'password' => 'password'
        ], $result);
    }

    /**
     * testWrite
     *
     * @return void
     */
    public function testWrite()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/']);
        $request = $request->withAttribute('session', $this->sessionMock);
        $response = new Response();
        $storage = new CakeSessionStorage();

        $data = new ArrayObject(['username' => 'florian']);
        $this->sessionMock->expects($this->at(0))
            ->method('write')
            ->with('Auth', $data);

        $result = $storage->write($request, $response, $data);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * testClear
     *
     * @return void
     */
    public function testClear()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/']);
        $request = $request->withAttribute('session', $this->sessionMock);
        $response = new Response();
        $storage = new CakeSessionStorage();

        $this->sessionMock->expects($this->at(0))
            ->method('delete')
            ->with('Auth');

        $result = $storage->clear($request, $response);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
}

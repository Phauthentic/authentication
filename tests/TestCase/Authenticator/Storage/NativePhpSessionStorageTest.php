<?php
declare(strict_types=1);
/**
 * Copyright (c) Phauthentic (https://github.com/Phauthentic)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Phauthentic (https://github.com/Phauthentic)
 * @link          https://github.com/Phauthentic
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Phauthentic\Authentication\Test\TestCase\Authenticator\Storage;

use Phauthentic\Authentication\Authenticator\Storage\NativePhpSessionStorage;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * NativePhpSessionStorageTest
 */
class NativePhpSessionStorageTest extends TestCase
{
    /**
     * Server Request Mock
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * Response Mock
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * @inheritDoc
     */
    public function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUp();

        $this->request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();

        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
    }

    /**
     * Test reading from an empty session
     *
     * @return void
     */
    public function testReadFromEmptySession()
    {
        unset($_SESSION);
        $storage = new NativePhpSessionStorage('Auth');
        $result = $storage->read($this->request);
        $this->assertNull($result);
    }

    /**
     * testReadAndWrite
     *
     * @return void
     */
    public function testReadAndWrite()
    {
        $data = ['username' => 'florian'];
        $storage = new NativePhpSessionStorage('Auth');

        $storage->write($this->request, $this->response, $data);
        $this->assertEquals($_SESSION['Auth'], $data);

        $result = $storage->read($this->request);
        $this->assertEquals($data, $result);

        $storage->clear($this->request,  $this->response);
        $result = $storage->read($this->request);
        $this->assertNull($result);
    }
}

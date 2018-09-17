<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         4.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\Test\TestCase\Authenticator;

use Authentication\Authenticator\Storage\CakeCookieStorage;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\ResponseInterface;

class CakeCookieStorageTest extends TestCase
{

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->skipIf(!class_exists(Cookie::class));

        parent::setUp();
    }

    /**
     * testRead
     *
     * @return void
     */
    public function testRead()
    {
        $storage = new CakeCookieStorage();

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            null,
            null,
            [
                'CookieAuth' => '["mariano","$2y$10$1bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS/KGmC1hNuWkUG7ES"]'
            ]
        );
        $result = $storage->read($request);
        $this->assertEquals(["mariano","$2y$10$1bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS/KGmC1hNuWkUG7ES"], $result);
    }

    /**
     * testReadExpandedCookie
     *
     * @return void
     */
    public function testReadExpandedCookie()
    {
        $storage = new CakeCookieStorage();

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            null,
            null,
            [
                'CookieAuth' => ["mariano","$2y$10$1bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS/KGmC1hNuWkUG7ES"]
            ]
        );
        $result = $storage->read($request);
        $this->assertEquals(["mariano","$2y$10$1bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS/KGmC1hNuWkUG7ES"], $result);
    }

    /**
     * testWrite
     *
     * @return void
     */
    public function testWrite()
    {
        $storage = new CakeCookieStorage();

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath']
        );
        $response = new Response();

        $result = $storage->write($request, $response, '["mariano","$2y$10$1bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS/KGmC1hNuWkUG7ES"]');

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertContains('CookieAuth=%5B%22mariano%22%2C%22%242y%2410%24', $result->getHeaderLine('Set-Cookie'));
    }

    /**
     * testWriteExpanded
     *
     * @return void
     */
    public function testWriteExpanded()
    {
        $storage = new CakeCookieStorage();

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath']
        );
        $response = new Response();

        $result = $storage->write($request, $response, ["mariano","$2y$10$1bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS/KGmC1hNuWkUG7ES"]);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertContains('CookieAuth=%5B%22mariano%22%2C%22%242y%2410%24', $result->getHeaderLine('Set-Cookie'));
    }

    /**
     * testClear
     *
     * @return void
     */
    public function testClear()
    {
        $storage = new CakeCookieStorage();

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath']
        );
        $response = new Response();

        $result = $storage->clear($request, $response);
        $this->assertInstanceOf(ResponseInterface::class, $result);

        $this->assertEquals('CookieAuth=; expires=Thu, 01-Jan-1970 00:00:01 UTC; path=/', $result->getHeaderLine('Set-Cookie'));
    }
}

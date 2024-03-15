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

namespace Phauthentic\Authentication\Test\TestCase\Authenticator;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Phauthentic\Authentication\Authenticator\Exception\UnauthorizedException;
use Phauthentic\Authentication\Authenticator\HttpBasicAuthenticator;
use Phauthentic\Authentication\Identifier\PasswordIdentifier;
use Phauthentic\Authentication\Test\Resolver\TestResolver;
use Phauthentic\Authentication\Test\TestCase\AuthenticationTestCase as TestCase;
use Phauthentic\PasswordHasher\DefaultPasswordHasher;
use Psr\Http\Message\ResponseInterface;

class HttpBasicAuthenticatorTest extends TestCase
{
    use ArraySubsetAsserts;

    private ResponseInterface $response;

    private HttpBasicAuthenticator $auth;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $resolver = new TestResolver($this->getConnection()->getConnection());
        $identifiers = new PasswordIdentifier($resolver, new DefaultPasswordHasher());
        $this->auth = new HttpBasicAuthenticator($identifiers);
        $this->response = $this->getMockResponse();
    }

    /**
     * test the authenticate method
     *
     * @return void
     */
    public function testAuthenticateNoData()
    {
        $request = $this->getMockRequest();
        $request->expects($this->any())
            ->method('getServerParams')
            ->willReturn([]);

        $result = $this->auth->authenticate($request, $this->response);
        $this->assertFalse($result->isValid());
    }

    /**
     * test the authenticate method
     *
     * @return void
     */
    public function testAuthenticateNoUsername()
    {
        $request = $this->getMockRequest();
        $request->expects($this->any())
            ->method('getServerParams')
            ->willReturn([
                'REQUEST_URI' => '/posts/index',
                'PHP_AUTH_PW' => 'foobar',
            ]);

        $result = $this->auth->authenticate($request, $this->response);
        $this->assertFalse($result->isValid());
    }

    /**
     * test the authenticate method
     *
     * @return void
     */
    public function testAuthenticateNoPassword()
    {
        $request = $this->getMockRequest();
        $request->expects($this->any())
            ->method('getServerParams')
            ->willReturn([
                'REQUEST_URI' => '/posts/index',
                'PHP_AUTH_USER' => 'robert',
            ]);

        $result = $this->auth->authenticate($request, $this->response);
        $this->assertFalse($result->isValid());
    }

    /**
     * test the authenticate method
     *
     * @return void
     */
    public function testAuthenticateInjection(): void
    {
        $request = $this->getMockRequest();
        $request->expects($this->any())
            ->method('getServerParams')
            ->willReturn([
                'REQUEST_URI' => '/posts/index',
                'PHP_AUTH_USER' => '> 1',
                'PHP_AUTH_PW' => "' OR 1 = 1"
            ]);

        $result = $this->auth->authenticate($request, $this->response);
        $this->assertFalse($result->isValid());
    }

    /**
     * Test that username of 0 works.
     *
     * @return void
     * @throws \Exception
     */
    public function testAuthenticateUsernameZero(): void
    {
        $request = $this->getMockRequest([
            'parsedBody' => [
                'user' => '0',
                'password' => 'robert'
            ],
        ]);
        $request->expects($this->any())
            ->method('getServerParams')
            ->willReturn([
                'REQUEST_URI' => '/posts/index',
                'SERVER_NAME' => 'localhost',
                'PHP_AUTH_USER' => '0',
                'PHP_AUTH_PW' => 'robert'
            ]);

        $expected = [
            'id' => 3,
            'username' => '0',
        ];
        $result = $this->auth->authenticate($request, $this->response);
        $this->assertTrue($result->isValid());
        $this->assertArraySubset($expected, $result->getData());
    }

    /**
     * test that challenge headers are sent when no credentials are found.
     *
     * @return void
     */
    public function testAuthenticateChallenge()
    {
        $request = $this->getMockRequest();
        $request->expects($this->any())
            ->method('getServerParams')
            ->willReturn([
                'REQUEST_URI' => '/posts/index',
                'SERVER_NAME' => 'localhost',
            ]);

        try {
            $this->auth->unauthorizedChallenge($request);
            $this->fail('Should challenge');
        } catch (UnauthorizedException $e) {
            $expected = ['WWW-Authenticate' => 'Basic realm="localhost"'];
            $this->assertEquals($expected, $e->getHeaders());
            $this->assertEquals(401, $e->getCode());
        }
    }

    /**
     * test authenticate success
     *
     * @return void
     */
    public function testAuthenticateSuccess()
    {
        $request = $this->getMockRequest();
        $request->expects($this->any())
            ->method('getServerParams')
            ->willReturn([
                'REQUEST_URI' => '/posts/index',
                'PHP_AUTH_USER' => 'robert',
                'PHP_AUTH_PW' => 'robert'
            ]);

        $result = $this->auth->authenticate($request, $this->response);
        $expected = [
            'id' => 2,
            'username' => 'robert',
        ];

        $this->assertTrue($result->isValid());
        $this->assertArraySubset($expected, $result->getData());
    }
}

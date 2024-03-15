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

use Phauthentic\Authentication\Authenticator\Result;
use Phauthentic\Authentication\Authenticator\TokenAuthenticator;
use Phauthentic\Authentication\Identifier\TokenIdentifier;
use Phauthentic\Authentication\Test\Resolver\TestResolver;
use Phauthentic\Authentication\Test\TestCase\AuthenticationTestCase as TestCase;

class TokenAuthenticatorTest extends TestCase
{
    protected $request;
    protected $identifier;
    protected $response;

    /**
     * Fixtures
     *
     * @var array
     */
    public array $fixtures = [
        'core.auth_users',
        'core.users'
    ];

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $resolver = new TestResolver($this->getConnection()->getConnection());
        $this->identifier = (new TokenIdentifier($resolver))->setTokenField('username');

        $this->request = $this->getMockRequest([
            'method' => 'GET',
            'path' => '/testpath',
            'parsedBody' => [
                'plugin' => null,
                'controller' => 'Users',
                'action' => 'token'
            ],
        ]);

        $this->response = $this->getMockResponse();
    }

    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticateViaHeaderToken(): void
    {
        // Test without token
        $tokenAuth = (new TokenAuthenticator($this->identifier))
            ->setQueryParam('token');

        $result = $tokenAuth->authenticate($this->request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_MISSING, $result->getStatus());

        $this->request->expects($this->any())
            ->method('getHeaderLine')
            ->with('Token')
            ->willReturn('florian');

        $tokenAuth = (new TokenAuthenticator($this->identifier))
            ->setHeaderName('Token');

        $result = $tokenAuth->authenticate($this->request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
    }

    /**
     * @return void
     */
    public function testValidQueryParamToken(): void
    {
        $request = $this->getMockRequest();
        $request->expects($this->any())
            ->method('getQueryParams')
            ->willReturn(['token' => 'florian']);

        $tokenAuth = (new TokenAuthenticator($this->identifier))
            ->setQueryParam('token');

        $result = $tokenAuth->authenticate($request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
    }

    /**
     * @return void
     */
    public function testInvalidQueryParamToken(): void
    {
        $request = $this->getMockRequest();
        $request->expects($this->any())
            ->method('getQueryParams')
            ->willReturn(['token' => 'does-not-exist']);

        $tokenAuth = (new TokenAuthenticator($this->identifier))
            ->setQueryParam('token');

        $result = $tokenAuth->authenticate($request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getStatus());
    }

    /**
     * @return void
     */
    public function testValidTokenPrefix(): void
    {
        $tokenAuth = (new TokenAuthenticator($this->identifier))
            ->setHeaderName('Token')
            ->setTokenPrefix('identity');

        $request = $this->getMockRequest();
        $request->expects($this->any())
            ->method('getHeaderLine')
            ->willReturn('identity florian');

        $result = $tokenAuth->authenticate($request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
    }

    /**
     * @return void
     */
    public function testInvalidTokenPrefix(): void
    {
        $request = $this->getMockRequest();
        $request->expects($this->any())
            ->method('getHeaderLine')
            ->willReturn('bearer florian');

        $tokenAuth = (new TokenAuthenticator($this->identifier))
            ->setHeaderName('Token')
            ->setTokenPrefix('identity');

        $result = $tokenAuth->authenticate($request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getStatus());
    }
}

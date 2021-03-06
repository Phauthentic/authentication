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
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class TokenAuthenticatorTest extends TestCase
{
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

        $this->request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'florian', 'password' => 'password']
        );

        $this->response = new Response();
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

        // Test header token
        $requestWithHeaders = $this->request->withAddedHeader('Token', 'florian');
        $tokenAuth = (new TokenAuthenticator($this->identifier))
            ->setHeaderName('Token');

        $result = $tokenAuth->authenticate($requestWithHeaders, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
    }

    /**
     * testViaQueryParamToken
     *
     * @return void
     */
    public function testViaQueryParamToken(): void
    {
        // Test with query param token
        $requestWithParams = $this->request->withQueryParams(['token' => 'florian']);
        $tokenAuth = (new TokenAuthenticator($this->identifier))
            ->setQueryParam('token');

        $result = $tokenAuth->authenticate($requestWithParams, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());

        // Test with valid query param but invalid token
        $requestWithParams = $this->request->withQueryParams(['token' => 'does-not-exist']);
        $tokenAuth = (new TokenAuthenticator($this->identifier))
            ->setQueryParam('token');

        $result = $tokenAuth->authenticate($requestWithParams, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getStatus());
    }

    /**
     * testTokenPrefix
     *
     * @return void
     */
    public function testTokenPrefix(): void
    {
        //valid prefix
        $requestWithHeaders = $this->request->withAddedHeader('Token', 'identity florian');
        $tokenAuth = (new TokenAuthenticator($this->identifier))
            ->setHeaderName('Token')
            ->setTokenPrefix('identity');

        $result = $tokenAuth->authenticate($requestWithHeaders, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());

        //invalid prefix
        $requestWithHeaders = $this->request->withAddedHeader('Token', 'bearer florian');
        $tokenAuth = (new TokenAuthenticator($this->identifier))
            ->setHeaderName('Token')
            ->setTokenPrefix('identity');

        $result = $tokenAuth->authenticate($requestWithHeaders, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getStatus());
    }
}

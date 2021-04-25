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

use ArrayAccess;
use ArrayObject;
use Phauthentic\Authentication\Authenticator\JwtAuthenticator;
use Phauthentic\Authentication\Authenticator\Result;
use Phauthentic\Authentication\Identifier\JwtSubjectIdentifier;
use Phauthentic\Authentication\Test\Resolver\TestResolver;
use Phauthentic\Authentication\Test\TestCase\AuthenticationTestCase as TestCase;
use Exception;
use Firebase\JWT\JWT;
use stdClass;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class JwtAuthenticatorTest extends TestCase
{

    /**
     * Test token
     *
     * @var string
     */
    public string $token;

    /**
     * Identifier Collection
     *
     * @var \Authentication\Identifier\IdentifierCollection;
     */
    public $identifiers;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $data = [
            'sub' => 3,
            'id' => 3,
            'username' => 'larry',
            'firstname' => 'larry'
        ];

        $this->token = JWT::encode($data, 'secretKey');
        $resolver = new TestResolver($this->getConnection()->getConnection());
        $this->identifiers = new JwtSubjectIdentifier($resolver);
        $this->response = new Response();
    }

    /**
     * testAuthenticateViaHeaderToken
     *
     * @return void
     */
    public function testAuthenticateViaHeaderToken()
    {
        $this->request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/']
        );
        $this->request = $this->request->withAddedHeader('Authorization', 'Bearer ' . $this->token);

        $authenticator = (new JwtAuthenticator($this->identifiers, 'secretKey'));

        $result = $authenticator->authenticate($this->request, $this->response);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
        $this->assertInstanceOf(ArrayAccess::class, $result->getData());
    }

    /**
     * testAuthenticateViaQueryParamToken
     *
     * @return void
     */
    public function testAuthenticateViaQueryParamToken(): void
    {
        $this->request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/'],
            ['token' => $this->token]
        );

        $authenticator = (new JwtAuthenticator($this->identifiers, 'secretKey'));

        $result = $authenticator->authenticate($this->request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
        $this->assertInstanceOf(ArrayAccess::class, $result->getData());
    }

    /**
     * testAuthenticationViaIdentifierAndSubject
     *
     * @return void
     */
    public function testAuthenticationViaIdentifierAndSubject(): void
    {
        $this->request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/'],
            ['token' => $this->token]
        );

        $this->identifiers = $this->createMock(JwtSubjectIdentifier::class);
        $this->identifiers->expects($this->once())
            ->method('identify')
            ->with([
                'sub' => 3
            ])
            ->willReturn(new ArrayObject([
                'sub' => 3,
                'id' => 3,
                'username' => 'larry',
                'firstname' => 'larry'
            ]));

        $authenticator = (new JwtAuthenticator($this->identifiers, 'secretKey'))
            ->setReturnPayload(false);

        $result = $authenticator->authenticate($this->request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
        $this->assertInstanceOf(ArrayAccess::class, $result->getData());
    }

    /**
     * Testing an invalid token
     *
     * The authenticator will turn the JWT libs exceptions into an error result.
     *
     * @return void
     */
    public function testAuthenticateInvalidPayloadNotAnObject(): void
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/'],
            ['token' => $this->token]
        );

        $response = new Response();

        $authenticator = $this->getMockBuilder(JwtAuthenticator::class)
            ->setConstructorArgs([
                $this->identifiers,
                'secretKey'
            ])
            ->setMethods([
                'getPayLoad'
            ])
            ->getMock();

        $authenticator->expects($this->once())
            ->method('getPayLoad')
            ->willReturn('no an object');

        $result = $authenticator->authenticate($request, $response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_INVALID, $result->getStatus());
        $this->assertNull($result->getData());
    }

    /**
     * testAuthenticateInvalidPayloadEmpty
     *
     * @return void
     */
    public function testAuthenticateInvalidPayloadEmpty(): void
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/'],
            ['token' => $this->token]
        );

        $response = new Response();

        $authenticator = $this->getMockBuilder(JwtAuthenticator::class)
            ->setConstructorArgs([
                $this->identifiers,
                'secretKey'
            ])
            ->setMethods([
                'getPayLoad'
            ])
            ->getMock();

        $authenticator->expects($this->once())
            ->method('getPayLoad')
            ->willReturn(new stdClass());

        $result = $authenticator->authenticate($request, $response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_MISSING, $result->getStatus());
        $this->assertNUll($result->getData());
    }

    public function testInvalidToken()
    {
        $this->request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/'],
            ['token' => 'should cause an exception']
        );

        $authenticator = (new JwtAuthenticator($this->identifiers, 'secretKey'));

        $result = $authenticator->authenticate($this->request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_INVALID, $result->getStatus());
        $this->assertNUll($result->getData());
        $errors = $result->getErrors();
        $this->assertArrayHasKey('message', $errors);
        $this->assertArrayHasKey('exception', $errors);
        $this->assertInstanceOf(Exception::class, $errors['exception']);
    }

    /**
     * testGetPayload
     *
     * @return void
     */
    public function testGetPayload()
    {
        $this->request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/'],
            ['token' => $this->token]
        );

        $authenticator = (new JwtAuthenticator($this->identifiers, 'secretKey'));

        $result = $authenticator->getPayload();
        $this->assertNull($result);

        $authenticator->authenticate($this->request, $this->response);

        $expected = [
            'sub' => 3,
            'id' => 3,
            'username' => 'larry',
            'firstname' => 'larry'
        ];

        $result = $authenticator->getPayload();
        $this->assertEquals($expected, (array)$result);
    }
}

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
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\Test\TestCase\Authenticator;

use ArrayAccess;
use ArrayObject;
use Authentication\Authenticator\JwtAuthenticator;
use Authentication\Authenticator\Result;
use Authentication\Identifier\IdentifierCollection;
use Authentication\Test\TestCase\AuthenticationTestCase as TestCase;
use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Exception;
use Firebase\JWT\JWT;

class JwtAuthenticatorTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'core.auth_users',
        'core.users'
    ];

    /**
     * Test token
     *
     * @var string
     */
    public $token;

    /**
     * Identifier Collection
     *
     * @var \Authentication\Identifier\IdentifierCollection;
     */
    public $identifiers;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $data = [
            'sub' => 3,
            'id' => 3,
            'username' => 'larry',
            'firstname' => 'larry'
        ];

        $this->token = JWT::encode($data, 'secretKey');
        $this->identifiers = new IdentifierCollection([]);
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

        $authenticator = new JwtAuthenticator($this->identifiers, [
            'secretKey' => 'secretKey'
        ]);

        $result = $authenticator->authenticate($this->request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getCode());
        $this->assertInstanceOf(ArrayAccess::class, $result->getData());
    }

    /**
     * testAuthenticateViaQueryParamToken
     *
     * @return void
     */
    public function testAuthenticateViaQueryParamToken()
    {
        $this->request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/'],
            ['token' => $this->token]
        );

        $authenticator = new JwtAuthenticator($this->identifiers, [
            'secretKey' => 'secretKey'
        ]);

        $result = $authenticator->authenticate($this->request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getCode());
        $this->assertInstanceOf(ArrayAccess::class, $result->getData());
    }

    /**
     * testAuthenticationViaIdentifierAndSubject
     *
     * @return void
     */
    public function testAuthenticationViaIdentifierAndSubject()
    {
        $this->request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/'],
            ['token' => $this->token]
        );

        $this->identifiers = $this->createMock(IdentifierCollection::class);
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

        $authenticator = new JwtAuthenticator($this->identifiers, [
            'secretKey' => 'secretKey',
            'returnPayload' => false
        ]);

        $result = $authenticator->authenticate($this->request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getCode());
        $this->assertInstanceOf(ArrayAccess::class, $result->getData());
    }

    /**
     * Testing an invalid token
     *
     * The authenticator will turn the JWT libs exceptions into an error result.
     *
     * @return void
     */
    public function testAuthenticateInvalidPayloadNotAnObject()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/'],
            ['token' => $this->token]
        );

        $response = new Response();

        $authenticator = $this->getMockBuilder(JwtAuthenticator::class)
            ->setConstructorArgs([
                $this->identifiers
            ])
            ->setMethods([
                'getPayLoad'
            ])
            ->getMock();

        $authenticator->expects($this->at(0))
            ->method('getPayLoad')
            ->will($this->returnValue('no an object'));

        $result = $authenticator->authenticate($request, $response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_INVALID, $result->getCode());
        $this->assertNull($result->getData());
    }

    /**
     * testAuthenticateInvalidPayloadEmpty
     *
     * @return void
     */
    public function testAuthenticateInvalidPayloadEmpty()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/'],
            ['token' => $this->token]
        );

        $response = new Response();

        $authenticator = $this->getMockBuilder(JwtAuthenticator::class)
            ->setConstructorArgs([
                $this->identifiers
            ])
            ->setMethods([
                'getPayLoad'
            ])
            ->getMock();

        $authenticator->expects($this->at(0))
            ->method('getPayLoad')
            ->will($this->returnValue(new \stdClass()));

        $result = $authenticator->authenticate($request, $response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_NOT_FOUND_IN_REQUEST, $result->getCode());
        $this->assertNUll($result->getData());
    }

    public function testInvalidToken()
    {
        $this->request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/'],
            ['token' => 'should cause an exception']
        );

        $authenticator = new JwtAuthenticator($this->identifiers, [
            'secretKey' => 'secretKey'
        ]);

        $result = $authenticator->authenticate($this->request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_INVALID, $result->getCode());
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

        $authenticator = new JwtAuthenticator($this->identifiers, [
            'secretKey' => 'secretKey'
        ]);

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

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

use Authentication\Authenticator\TokenAuthenticator;
use Authentication\Identifier\IdentifierCollection;
use Authentication\Result;
use Authentication\Test\TestCase\AuthenticationTestCase as TestCase;
use Cake\Http\ServerRequestFactory;
use Zend\Diactoros\Response;

class TokenAuthenticatorTest extends TestCase
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
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticate()
    {
        $identifiers = new IdentifierCollection([
           'Authentication.Token' => [
               'tokenField' => 'username'
           ]
        ]);

        // Prepare request and response
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'mariano', 'password' => 'password']
        );
        $response = new Response('php://memory', 200, ['X-testing' => 'Yes']);

        // Test without token
        $tokenAuth = new TokenAuthenticator($identifiers, [
            'queryParam' => 'token'
        ]);
        $result = $tokenAuth->authenticate($request, $response);
        $this->assertInstanceOf('\Authentication\Result', $result);
        $this->assertEquals(Result::FAILURE_OTHER, $result->getCode());

        // Test header token
        $requestWithHeaders = $request->withAddedHeader('Token', 'mariano');
        $tokenAuth = new TokenAuthenticator($identifiers, [
            'header' => 'Token'
        ]);
        $result = $tokenAuth->authenticate($requestWithHeaders, $response);
        $this->assertInstanceOf('\Authentication\Result', $result);
        $this->assertEquals(Result::SUCCESS, $result->getCode());

        // Test with query param token
        $requestWithParams = $request->withQueryParams(['token' => 'mariano']);
        $tokenAuth = new TokenAuthenticator($identifiers, [
            'queryParam' => 'token'
        ]);
        $result = $tokenAuth->authenticate($requestWithParams, $response);
        $this->assertInstanceOf('\Authentication\Result', $result);
        $this->assertEquals(Result::SUCCESS, $result->getCode());

        // Test with valid query param but invalid token
        $requestWithParams = $request->withQueryParams(['token' => 'does-not-exist']);
        $tokenAuth = new TokenAuthenticator($identifiers, [
            'queryParam' => 'token'
        ]);
        $result = $tokenAuth->authenticate($requestWithParams, $response);
        $this->assertInstanceOf('\Authentication\Result', $result);
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getCode());
    }
}

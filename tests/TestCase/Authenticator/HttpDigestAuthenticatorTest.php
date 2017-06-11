<?php
/**
 * HttpDigestAuthenticatorTest file
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\Test\TestCase\Authentication;

use Authentication\Authenticator\HttpDigestAuthenticator;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\UnauthorizedException;
use Authentication\Identifier\IdentifierCollection;
use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Test case for HttpDigestAuthentication
 */
class HttpDigestAuthenticatorTest extends TestCase
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
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->identifiers = new IdentifierCollection([
           'Authentication.Password'
        ]);

        $this->auth = new HttpDigestAuthenticator($this->identifiers, [
            'realm' => 'localhost',
            'nonce' => 123,
            'opaque' => '123abc'
        ]);

        $password = HttpDigestAuthenticator::password('mariano', 'cake', 'localhost');
        $User = TableRegistry::get('Users');
        $User->updateAll(['password' => $password], []);

        $this->response = $this->getMockBuilder(Response::class)->getMock();
    }

    /**
     * test applying settings in the constructor
     *
     * @return void
     */
    public function testConstructor()
    {
        $object = new HttpDigestAuthenticator($this->identifiers, [
            'userModel' => 'AuthUser',
            'fields' => ['username' => 'user', 'password' => 'pass'],
            'nonce' => 123456
        ]);

        $this->assertEquals('AuthUser', $object->config('userModel'));
        $this->assertEquals(['username' => 'user', 'password' => 'pass'], $object->config('fields'));
        $this->assertEquals(123456, $object->config('nonce'));
        $this->assertEquals(env('SERVER_NAME'), $object->config('realm'));
    }

    /**
     * test the authenticate method
     *
     * @return void
     */
    public function testAuthenticateNoData()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/posts/index']
        );

        $result = $this->auth->authenticate($request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isValid());
    }

    /**
     * test the authenticate method
     *
     * @return void
     */
    public function testAuthenticateWrongUsername()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/posts/index'],
            [],
            []
        );

        $digest = <<<DIGEST
Digest username="incorrect_user",
realm="localhost",
nonce="123456",
uri="/dir/index.html",
qop=auth,
nc=00000001,
cnonce="0a4f113b",
response="6629fae49393a05397450978507c4ef1",
opaque="123abc"
DIGEST;
        $_SERVER['PHP_AUTH_DIGEST'] = $digest;

        try {
            $this->auth->unauthorizedChallenge($request);
            $this->fail('Should throw an exception');
        } catch (UnauthorizedException $e) {
            $this->assertSame(401, $e->getCode());
            $this->assertEquals(
                'Digest realm="localhost",qop="auth",nonce="123",opaque="123abc"',
                $e->getHeaders()['WWW-Authenticate']
            );
        }
    }

    /**
     * test that challenge headers are sent when no credentials are found.
     *
     * @return void
     */
    public function testAuthenticateChallenge()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/posts/index', 'REQUEST_METHOD' => 'GET']
        );

        try {
            $this->auth->unauthorizedChallenge($request);
            $this->fail('Should challenge');
        } catch (UnauthorizedException $e) {
            $expected = ['WWW-Authenticate' => 'Digest realm="localhost",qop="auth",nonce="123",opaque="123abc"'];
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
        $digest = <<<DIGEST
Digest username="mariano",
realm="localhost",
nonce="123",
uri="/dir/index.html",
qop=auth,
nc=1,
cnonce="123",
response="06b257a54befa2ddfb9bfa134224aa29",
opaque="123abc"
DIGEST;

        $request = ServerRequestFactory::fromGlobals(
            [
                'SERVER_NAME' => 'localhost',
                'REQUEST_URI' => '/dir/index.html',
                'REQUEST_METHOD' => 'GET',
                'PHP_AUTH_DIGEST' => $digest
            ]
        );

        $result = $this->auth->authenticate($request, $this->response);

        $expected = [
            'id' => 1,
            'username' => 'mariano',
            'created' => new Time('2007-03-17 01:16:23'),
            'updated' => new Time('2007-03-17 01:18:31')
        ];
        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertEquals($expected, $result->getIdentity()->toArray());
    }

    /**
     * test authenticate success
     *
     * @return void
     */
    public function testAuthenticateSuccessSimulatedRequestMethod()
    {
        $digest = <<<DIGEST
Digest username="mariano",
realm="localhost",
nonce="123",
uri="/dir/index.html",
qop=auth,
nc=1,
cnonce="123",
response="06b257a54befa2ddfb9bfa134224aa29",
opaque="123abc"
DIGEST;

        $request = ServerRequestFactory::fromGlobals(
            [
                'SERVER_NAME' => 'localhost',
                'REQUEST_URI' => '/posts/index',
                'REQUEST_METHOD' => 'GET',
                'PHP_AUTH_DIGEST' => $digest
            ]
        );

        $result = $this->auth->authenticate($request, $this->response);
        $expected = [
            'id' => 1,
            'username' => 'mariano',
            'created' => new Time('2007-03-17 01:16:23'),
            'updated' => new Time('2007-03-17 01:18:31')
        ];
        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertEquals($expected, $result->getIdentity()->toArray());
    }

    /**
     * test scope failure.
     *
     * @return void
     */
    public function testAuthenticateFailReChallenge()
    {
        $this->auth->config('scope.username', 'nate');

        $digest = <<<DIGEST
Digest username="mariano",
realm="localhost",
nonce="123",
uri="/dir/index.html",
qop=auth,
nc=1,
cnonce="123",
response="6629fae49393a05397450978507c4ef1",
opaque="123abc"
DIGEST;

        $request = ServerRequestFactory::fromGlobals(
            [
                'REQUEST_URI' => '/posts/index',
                'REQUEST_METHOD' => 'GET',
                'PHP_AUTH_DIGEST' => $digest
            ]
        );

        try {
            $this->auth->unauthorizedChallenge($request);
            $this->fail('Should throw an exception');
        } catch (UnauthorizedException $e) {
            $this->assertSame(401, $e->getCode());
            $this->assertEquals(
                'Digest realm="localhost",qop="auth",nonce="123",opaque="123abc"',
                $e->getHeaders()['WWW-Authenticate']
            );
        }
    }

    /**
     * testParseDigestAuthData method
     *
     * @return void
     */
    public function testParseAuthData()
    {
        $digest = <<<DIGEST
            Digest username="Mufasa",
            realm="testrealm@host.com",
            nonce="dcd98b7102dd2f0e8b11d0f600bfb0c093",
            uri="/dir/index.html?query=string&value=some%20value",
            qop=auth,
            nc=00000001,
            cnonce="0a4f113b",
            response="6629fae49393a05397450978507c4ef1",
            opaque="5ccc069c403ebaf9f0171e9517f40e41"
DIGEST;
        $expected = [
            'username' => 'Mufasa',
            'realm' => 'testrealm@host.com',
            'nonce' => 'dcd98b7102dd2f0e8b11d0f600bfb0c093',
            'uri' => '/dir/index.html?query=string&value=some%20value',
            'qop' => 'auth',
            'nc' => '00000001',
            'cnonce' => '0a4f113b',
            'response' => '6629fae49393a05397450978507c4ef1',
            'opaque' => '5ccc069c403ebaf9f0171e9517f40e41'
        ];
        $result = $this->auth->parseAuthData($digest);
        $this->assertSame($expected, $result);

        $result = $this->auth->parseAuthData('');
        $this->assertNull($result);
    }

    /**
     * Test parsing a full URI. While not part of the spec some mobile clients will do it wrong.
     *
     * @return void
     */
    public function testParseAuthDataFullUri()
    {
        $digest = <<<DIGEST
            Digest username="admin",
            realm="192.168.0.2",
            nonce="53a7f9b83f61b",
            uri="http://192.168.0.2/pvcollection/sites/pull/HFD%200001.json#fragment",
            qop=auth,
            nc=00000001,
            cnonce="b85ff144e496e6e18d1c73020566ea3b",
            response="5894f5d9cd41d012bac09eeb89d2ddf2",
            opaque="6f65e91667cf98dd13464deaf2739fde"
DIGEST;

        $expected = 'http://192.168.0.2/pvcollection/sites/pull/HFD%200001.json#fragment';
        $result = $this->auth->parseAuthData($digest);
        $this->assertSame($expected, $result['uri']);
    }

    /**
     * test parsing digest information with email addresses
     *
     * @return void
     */
    public function testParseAuthEmailAddress()
    {
        $digest = <<<DIGEST
            Digest username="mark@example.com",
            realm="testrealm@host.com",
            nonce="dcd98b7102dd2f0e8b11d0f600bfb0c093",
            uri="/dir/index.html",
            qop=auth,
            nc=00000001,
            cnonce="0a4f113b",
            response="6629fae49393a05397450978507c4ef1",
            opaque="5ccc069c403ebaf9f0171e9517f40e41"
DIGEST;
        $expected = [
            'username' => 'mark@example.com',
            'realm' => 'testrealm@host.com',
            'nonce' => 'dcd98b7102dd2f0e8b11d0f600bfb0c093',
            'uri' => '/dir/index.html',
            'qop' => 'auth',
            'nc' => '00000001',
            'cnonce' => '0a4f113b',
            'response' => '6629fae49393a05397450978507c4ef1',
            'opaque' => '5ccc069c403ebaf9f0171e9517f40e41'
        ];
        $result = $this->auth->parseAuthData($digest);
        $this->assertSame($expected, $result);
    }

    /**
     * test password hashing
     *
     * @return void
     */
    public function testPassword()
    {
        $result = HttpDigestAuthenticator::password('mark', 'password', 'localhost');
        $expected = md5('mark:localhost:password');
        $this->assertEquals($expected, $result);
    }
}

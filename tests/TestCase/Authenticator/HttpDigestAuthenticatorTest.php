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

namespace Phauthentic\Authentication\Test\TestCase\Authentication;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Phauthentic\Authentication\Authenticator\Exception\UnauthorizedException;
use Phauthentic\Authentication\Authenticator\HttpDigestAuthenticator;
use Phauthentic\Authentication\Authenticator\Result;
use Phauthentic\Authentication\Authenticator\StatelessInterface;
use Phauthentic\Authentication\Identifier\PasswordIdentifier;
use Phauthentic\Authentication\Test\Resolver\TestResolver;
use Phauthentic\Authentication\Test\TestCase\AuthenticationTestCase as TestCase;
use Phauthentic\PasswordHasher\DefaultPasswordHasher;

/**
 * Test case for HttpDigestAuthentication
 */
class HttpDigestAuthenticatorTest extends TestCase
{
    use ArraySubsetAsserts;

    /**
     * @var \Phauthentic\Authentication\Identifier\PasswordIdentifier
     */
    private PasswordIdentifier $identifiers;
    /**
     * @var \Phauthentic\Authentication\Authenticator\HttpDigestAuthenticator
     */
    private HttpDigestAuthenticator $auth;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Zend\Diactoros\Response
     */
    private $response;

    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $resolver = new TestResolver($this->getConnection()->getConnection());
        $this->identifiers = new PasswordIdentifier($resolver, new DefaultPasswordHasher());

        $this->auth = (new HttpDigestAuthenticator($this->identifiers))
            ->setRealm('localhost')
            ->setOpaque('123abc');

        $this->response = $this->getMockResponse();
    }

    /**
     * test applying settings in the constructor
     *
     * @return void
     */
    public function testConstructor(): void
    {
        $object = (new HttpDigestAuthenticator($this->identifiers))
            ->setCredentialFields('user', 'pass');

        $this->assertInstanceOf(StatelessInterface::class, $object, 'Should be a stateless authenticator');
    }

    /**
     * test the authenticate method
     *
     * @return void
     */
    public function testAuthenticateNoData(): void
    {
        $request = $this->getMockRequest([
            'path' => '/posts/index'
        ]);

        $result = $this->auth->authenticate($request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isValid());
    }

    /**
     * test the authenticate method
     *
     * @return void
     */
    public function testAuthenticateWrongUsername(): void
    {
        $request = $this->getMockRequest([
            'path' => '/posts/index'
        ]);

        $digest = <<<DIGEST
Digest username="incorrect_user",
realm="localhost",
nonce="{$this->generateNonce()}",
uri="/dir/index.html",
qop=auth,
nc=00000001,
cnonce="0a4f113b",
response="6629fae49393a05397450978507c4ef1",
opaque="123abc"
DIGEST;
        $_SERVER['PHP_AUTH_DIGEST'] = $digest;

        $result = $this->auth->authenticate($request, $this->response);
        $this->assertFalse($result->isValid(), 'Should fail');
    }

    /**
     * test authenticate success
     *
     * @return void
     * @throws \Exception
     */
    public function testAuthenticateSuccess()
    {
        $data = [
            'username' => 'digest',
            'uri' => '/dir/index.html',
            'nonce' => $this->generateNonce(),
            'nc' => 1,
            'cnonce' => '123',
            'qop' => 'auth',
        ];

        $data['response'] = $this->auth->generateResponseHash(
            $data,
            HttpDigestAuthenticator::generatePasswordHash(
                'digest',
                'password',
                'localhost'
            ),
            'GET'
        );

        $request = $this->getMockRequest([
            'path' => '/dir/index.html',
            'method' => 'GET',
        ]);

        $request->expects($this->any())
            ->method('getServerParams')
            ->willReturn([
                'REQUEST_METHOD' => 'GET',
                'SERVER_NAME' => 'localhost',
                'PHP_AUTH_DIGEST' => $this->digestHeader($data),
            ]);

        $result = $this->auth->authenticate($request, $this->response);
        $expected = [
            'id' => 4,
            'username' => 'digest',
        ];
        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertArraySubset($expected, $result->getData());
    }

    /**
     * test authenticate with garbage nonce
     *
     * @return void
     */
    public function testAuthenticateFailsOnBadNonce(): void
    {
        $data = [
            'username' => 'digest',
            'uri' => '/dir/index.html',
            'nonce' => 'notbase64data',
            'nc' => 1,
            'cnonce' => '123',
            'qop' => 'auth',
        ];

        $data['response'] = $this->auth->generateResponseHash($data, '09faa9931501bf30f0d4253fa7763022', 'GET');

        $request = $this->getMockRequest([
            'path' => '/dir/index.html',
            'method' => 'GET',
        ]);

        $request->expects($this->any())
            ->method('getServerParams')
            ->willReturn([
                'REQUEST_METHOD' => 'GET',
                'SERVER_NAME' => 'localhost',
                'PHP_AUTH_DIGEST' => $this->digestHeader($data),
            ]);

        $result = $this->auth->authenticate($request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isValid());
    }

    /**
     * test authenticate fails with a nonce that has too many parts
     *
     * @return void
     */
    public function testAuthenticateFailsNonceWithTooManyParts(): void
    {
        $data = [
            'username' => 'digest',
            'uri' => '/dir/index.html',
            'nonce' => base64_encode(time() . ':lol:lol'),
            'nc' => 1,
            'cnonce' => '123',
            'qop' => 'auth',
        ];

        $data['response'] = $this->auth->generateResponseHash(
            $data,
            '09faa9931501bf30f0d4253fa7763022',
            'GET'
        );

        $request = $this->getMockRequest([
            'path' => '/dir/index.html',
            'method' => 'GET',
        ]);

        $request->expects($this->any())
            ->method('getServerParams')
            ->willReturn([
                'REQUEST_METHOD' => 'GET',
                'SERVER_NAME' => 'localhost',
                'PHP_AUTH_DIGEST' => $this->digestHeader($data),
            ]);


        $result = $this->auth->authenticate($request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isValid());
    }

    /**
     * Test that authentication fails when a nonce is stale
     *
     * @return void
     */
    public function testAuthenticateFailsOnStaleNonce(): void
    {

        $data = [
            'uri' => '/dir/index.html',
            'nonce' => $this->generateNonce(null, 5, strtotime('-10 minutes')),
            'nc' => 1,
            'cnonce' => '123',
            'qop' => 'auth',
        ];

        $data['response'] = $this->auth->generateResponseHash($data, '09faa9931501bf30f0d4253fa7763022', 'GET');

        $request = $this->getMockRequest([
            'path' => '/posts/index',
            'method' => 'GET',
        ]);

        $request->expects($this->any())
            ->method('getServerParams')
            ->willReturn([
                'REQUEST_METHOD' => 'GET',
                'SERVER_NAME' => 'localhost',
                'PHP_AUTH_DIGEST' => $this->digestHeader($data),
            ]);

        $result = $this->auth->authenticate($request, $this->response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isValid());
    }

    /**
     * test that challenge headers are sent when no credentials are found.
     *
     * @return void
     */
    public function testUnauthorizedChallenge()
    {
        $request = $this->getMockRequest([
            'path' => '/posts/index',
        ]);

        $request->expects($this->any())
            ->method('getServerParams')
            ->willReturn([
                'REQUEST_METHOD' => 'GET',
                'SERVER_NAME' => 'localhost',
            ]);

        try {
            $this->auth->unauthorizedChallenge($request);
            $this->fail('Should challenge');
        } catch (UnauthorizedException $e) {
            $this->assertEquals(401, $e->getCode());
            $header = $e->getHeaders()['WWW-Authenticate'];
            $this->assertMatchesRegularExpression(
                '/^Digest realm="localhost",qop="auth",nonce="[A-Za-z0-9=]+",opaque="123abc"/',
                $header
            );
        }
    }

    /**
     * test scope failure.
     *
     * @return void
     */
    public function testUnauthorizedFailReChallenge()
    {
        $nonce = $this->generateNonce();
        $digest = <<<DIGEST
Digest username="digest",
realm="localhost",
nonce="{$this->generateNonce()}",
uri="/dir/index.html",
qop=auth,
nc=1,
cnonce="abc123",
response="6629fae49393a05397450978507c4ef1",
opaque="123abc"
DIGEST;

        $request = $this->getMockRequest([
            'path' => '/posts/index',
        ]);

        $request->expects($this->any())
            ->method('getServerParams')
            ->willReturn(['REQUEST_METHOD' => 'GET',
            'PHP_AUTH_DIGEST' => $digest
            ]);

        try {
            $this->auth->unauthorizedChallenge($request);
            $this->fail('Should throw an exception');
        } catch (UnauthorizedException $e) {
            $this->assertSame(401, $e->getCode());
            $header = $e->getHeaders()['WWW-Authenticate'];
            $this->assertMatchesRegularExpression(
                '/^Digest realm="localhost",qop="auth",nonce="[A-Za-z0-9=]+",opaque="123abc"/',
                $header
            );
        }
    }

    /**
     * test that challenge headers include stale when the nonce is stale
     *
     * @return void
     */
    public function testUnauthorizedChallengeIncludesStaleAttributeOnStaleNonce()
    {
        $data = [
            'uri' => '/dir/index.html',
            'nonce' => $this->generateNonce(null, 5, strtotime('-10 minutes')),
            'nc' => 1,
            'cnonce' => '123',
            'qop' => 'auth',
        ];

        $data['response'] = $this->auth->generateResponseHash($data, '09faa9931501bf30f0d4253fa7763022', 'GET');

        $request = $this->getMockRequest([
            'path' => '/posts/index',
        ]);

        $request->expects($this->any())
            ->method('getServerParams')
            ->willReturn([
                'REQUEST_METHOD' => 'GET',
                'PHP_AUTH_DIGEST' => $this->digestHeader($data)
            ]);


        try {
            $this->auth->unauthorizedChallenge($request);
        } catch (UnauthorizedException $e) {
        }
        $this->assertNotEmpty($e);

        $header = $e->getHeaders()['WWW-Authenticate'];
        $this->assertStringContainsString('stale=true', $header);
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
    public function testPassword(): void
    {
        $result = HttpDigestAuthenticator::generatePasswordHash('mark', 'password', 'localhost');
        $expected = md5('mark:localhost:password');

        $this->assertEquals($expected, $result);
    }

    /**
     * Create a digest header string from an array of data.
     *
     * @param array $data the data to convert into a header.
     * @return string
     */
    protected function digestHeader(array $data): string
    {
        $data += [
            'username' => 'digest',
            'realm' => 'localhost',
            'opaque' => '123abc'
        ];

        $digest = <<<DIGEST
Digest username="{$data['username']}",
realm="{$data['realm']}",
nonce="{$data['nonce']}",
uri="{$data['uri']}",
qop={$data['qop']},
nc={$data['nc']},
cnonce="{$data['cnonce']}",
response="{$data['response']}",
opaque="{$data['opaque']}"
DIGEST;

        return $digest;
    }

    /**
     * Generate a nonce value.
     *
     * @param string|null $secret The secret to use
     * @param int $expires Number of seconds the nonce is valid for
     * @param null $time The current time.
     * @return string
     */
    protected function generateNonce($secret = null, $expires = 300, $time = null): string
    {
        $time = $time ?: microtime(true);
        $expiryTime = $time + $expires;
        $signatureValue = hash_hmac('sha1', $expiryTime . ':' . $secret, $secret);
        $nonceValue = $expiryTime . ':' . $signatureValue;

        return base64_encode($nonceValue);
    }
}

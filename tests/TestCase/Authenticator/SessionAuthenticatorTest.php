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
namespace Authentication\Test\TestCase\Authenticator;

use ArrayObject;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\SessionAuthenticator;
use Authentication\Authenticator\Storage\StorageInterface;
use Authentication\Identifier\PasswordIdentifier;
use Authentication\Test\Resolver\TestResolver;
use Authentication\Test\TestCase\AuthenticationTestCase as TestCase;
use Phauthentic\PasswordHasher\DefaultPasswordHasher;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class SessionAuthenticatorTest extends TestCase
{

    /**
     * @param StorageInterface $storage Storage instance.
     * @return SessionAuthenticator
     */
    protected function createAuthenticator(StorageInterface $storage)
    {
        $hasher = new DefaultPasswordHasher();
        $resolver = new TestResolver($this->getConnection()->getConnection());
        $identifiers = new PasswordIdentifier($resolver, $hasher);

        return new SessionAuthenticator($identifiers, $storage);
    }

    /**
     * Test authentication
     *
     * @return void
     */
    public function testAuthenticate()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/']);
        $response = new Response();

        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->expects($this->once())
            ->method('read')
            ->with($request)
            ->willReturn([
                'username' => 'robert',
                'password' => 'h45h'
            ]);

        $authenticator = $this->createAuthenticator($storage);
        $result = $authenticator->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
    }

    /**
     * Test authentication
     *
     * @return void
     */
    public function testAuthenticateMissing()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/']);
        $response = new Response();

        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->expects($this->once())
            ->method('read')
            ->with($request)
            ->willReturn(null);

        $authenticator = $this->createAuthenticator($storage);
        $result = $authenticator->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getStatus());
    }

    /**
     * Test session data verification by database lookup
     *
     * @return void
     */
    public function testVerifyByDatabase()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/']);
        $response = new Response();

        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->expects($this->once())
            ->method('read')
            ->with($request)
            ->willReturn([
                'username' => 'robert',
                'password' => 'h45h'
            ]);

        $authenticator = $this->createAuthenticator($storage);
        $authenticator->enableVerification();

        $result = $authenticator->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
    }

    /**
     * Test session data verification by database lookup
     *
     * @return void
     */
    public function testVerifyByDatabaseInvalid()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/']);
        $response = new Response();

        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->expects($this->once())
            ->method('read')
            ->with($request)
            ->willReturn([
                'username' => 'does-not',
                'password' => 'exist'
            ]);

        $authenticator = $this->createAuthenticator($storage);
        $authenticator->enableVerification();

        $result = $authenticator->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_INVALID, $result->getStatus());
    }

    /**
     * testPersistIdentity
     *
     * @return void
     */
    public function testPersistIdentity()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/']);
        $response = new Response();
        $data = new ArrayObject(['username' => 'florian']);

        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->expects($this->once())
            ->method('write')
            ->with($request, $response, $data);

        $authenticator = $this->createAuthenticator($storage);

        $result = $authenticator->persistIdentity($request, $response, $data);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * testClearIdentity
     *
     * @return void
     */
    public function testClearIdentity()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/']);
        $response = new Response();

        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())->method('clear');

        $authenticator = $this->createAuthenticator($storage);

        $result = $authenticator->clearIdentity($request, $response);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
}

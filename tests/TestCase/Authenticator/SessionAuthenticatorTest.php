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

use ArrayObject;
use Phauthentic\Authentication\Authenticator\Result;
use Phauthentic\Authentication\Authenticator\SessionAuthenticator;
use Phauthentic\Authentication\Authenticator\Storage\StorageInterface;
use Phauthentic\Authentication\Identifier\PasswordIdentifier;
use Phauthentic\Authentication\Test\Resolver\TestResolver;
use Phauthentic\Authentication\Test\TestCase\AuthenticationTestCase as TestCase;
use Phauthentic\PasswordHasher\DefaultPasswordHasher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SessionAuthenticatorTest extends TestCase
{
    protected ServerRequestInterface $request;
    protected ResponseInterface $response;

    public function setUp(): void
    {
        parent::setUp();

        $this->request = $this->getMockRequest();
        $this->response = $this->getMockResponse();
    }

    /**
     * @param StorageInterface $storage Storage instance.
     * @return SessionAuthenticator
     */
    protected function createAuthenticator(StorageInterface $storage): SessionAuthenticator
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
    public function testAuthenticate(): void
    {


        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->expects($this->once())
            ->method('read')
            ->with($this->request)
            ->willReturn([
                'username' => 'robert',
                'password' => 'h45h'
            ]);

        $authenticator = $this->createAuthenticator($storage);
        $result = $authenticator->authenticate($this->request, $this->response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
    }

    /**
     * Test authentication
     *
     * @return void
     */
    public function testAuthenticateMissing(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->expects($this->once())
            ->method('read')
            ->with($this->request)
            ->willReturn(null);

        $authenticator = $this->createAuthenticator($storage);
        $result = $authenticator->authenticate($this->request, $this->response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getStatus());
    }

    /**
     * Test session data verification by database lookup
     *
     * @return void
     */
    public function testVerifyByDatabase(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->expects($this->once())
            ->method('read')
            ->with($this->request)
            ->willReturn([
                'username' => 'robert',
                'password' => 'h45h'
            ]);

        $authenticator = $this->createAuthenticator($storage);
        $authenticator->enableVerification();

        $result = $authenticator->authenticate($this->request, $this->response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
    }

    /**
     * Test session data verification by database lookup
     *
     * @return void
     */
    public function testVerifyByDatabaseInvalid(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->expects($this->once())
            ->method('read')
            ->with($this->request)
            ->willReturn([
                'username' => 'does-not',
                'password' => 'exist'
            ]);

        $authenticator = $this->createAuthenticator($storage);
        $authenticator->enableVerification();

        $result = $authenticator->authenticate($this->request, $this->response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_INVALID, $result->getStatus());
    }

    /**
     * testPersistIdentity
     *
     * @return void
     */
    public function testPersistIdentity(): void
    {
        $data = new ArrayObject(['username' => 'florian']);

        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->expects($this->once())
            ->method('write')
            ->with($this->request, $this->response, $data);

        $authenticator = $this->createAuthenticator($storage);

        $result = $authenticator->persistIdentity($this->request, $this->response, $data);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * testClearIdentity
     *
     * @return void
     */
    public function testClearIdentity()
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())->method('clear');

        $authenticator = $this->createAuthenticator($storage);

        $result = $authenticator->clearIdentity($this->request, $this->response);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
}

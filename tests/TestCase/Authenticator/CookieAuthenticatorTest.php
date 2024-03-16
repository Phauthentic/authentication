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

namespace Phauthentic\Authentication\Test\TestCase\Authenticator;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Phauthentic\Authentication\Authenticator\CookieAuthenticator;
use Phauthentic\Authentication\Authenticator\Result;
use Phauthentic\Authentication\Authenticator\Storage\StorageInterface;
use Phauthentic\Authentication\Identifier\PasswordIdentifier;
use Phauthentic\Authentication\Test\Resolver\TestResolver;
use Phauthentic\Authentication\Test\TestCase\AuthenticationTestCase;
use Phauthentic\Authentication\UrlChecker\DefaultUrlChecker;
use Phauthentic\PasswordHasher\DefaultPasswordHasher;
use Psr\Http\Message\ResponseInterface;

class CookieAuthenticatorTest extends AuthenticationTestCase
{
    /**
     * @param StorageInterface $storage Storage instance.
     * @return CookieAuthenticator
     */
    protected function createAuthenticator(StorageInterface $storage): CookieAuthenticator
    {
        $hasher = new DefaultPasswordHasher();
        $resolver = new TestResolver($this->getConnection()->getConnection());
        $identifiers = new PasswordIdentifier($resolver, $hasher);

        return new CookieAuthenticator($identifiers, $storage, $hasher, new DefaultUrlChecker());
    }

    /**
     * testAuthenticateInvalidTokenMissingUsername
     *
     * @return void
     */
    public function testAuthenticateInvalidTokenMissingUsername(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('read')
            ->willReturn(["$2y$10$2sqDmq10vv7cbIsnRymfhe0Hii.eabOK0x1WVWSn8pL1csV6NnwV2"]);

        $authenticator = $this->createAuthenticator($storage);

        $request = $this->getMockRequest();
        $response = $this->getMockResponse();

        $result = $authenticator->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_INVALID, $result->getStatus());
    }

    /**
     * testAuthenticateSuccess
     *
     * @return void
     */
    public function testAuthenticateSuccess(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('read')
            ->willReturn([
                'robert',
                '$2y$10$2sqDmq10vv7cbIsnRymfhe0Hii.eabOK0x1WVWSn8pL1csV6NnwV2'
            ]);

        $authenticator = $this->createAuthenticator($storage);

        $request = $this->getMockRequest();
        $response = $this->getMockResponse();

        $result = $authenticator->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
    }

    /**
     * testAuthenticateUnknownUser
     *
     * @return void
     */
    public function testAuthenticateUnknownUser(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('read')
            ->willReturn(["unknown","$2y$10$2sqDmq10vv7cbIsnRymfhe0Hii.eabOK0x1WVWSn8pL1csV6NnwV2"]);
        $authenticator = $this->createAuthenticator($storage);

        $request = $this->getMockRequest();
        $response = $this->getMockResponse();

        $result = $authenticator->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getStatus());
    }

    /**
     * testCredentialsNotPresent
     *
     * @return void
     */
    public function testCredentialsNotPresent(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('read')
            ->willReturn(null);
        $authenticator = $this->createAuthenticator($storage);

        $request = $this->getMockRequest();
        $response = $this->getMockResponse();

        $result = $authenticator->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_MISSING, $result->getStatus());
    }

    /**
     * testAuthenticateInvalidToken
     *
     * @return void
     */
    public function testAuthenticateInvalidToken(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('read')
            ->willReturn(["robert","$2y$10$1bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS/asdasdsadasd"]);
        $authenticator = $this->createAuthenticator($storage);

        $request = $this->getMockRequest();
        $response = $this->getMockResponse();

        $result = $authenticator->authenticate($request, $response);

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
        $response = $this->getMockResponse();
        $response->expects($this->once())
            ->method('getHeaderLine', 'Set-Cookie')
            ->willReturn('CookieAuth=%5B%22robert%22%2C%22%242y%2410%241bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS%5C%2FKGmC1hNuWkUG7ES%22%5D; path=/');

        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('write')
            ->willReturn($response);

        $authenticator = $this->createAuthenticator($storage);

        $request = $this->getMockRequest([
            'parsedBody' => [
                'remember_me' => 1
            ]
        ]);

        $identity = $this->getIdentity();
        $result = $authenticator->persistIdentity($request, $response, $identity);

        $this->assertStringContainsString(
            'CookieAuth=%5B%22robert%22%2C%22%242y%2410%24',
            $result->getHeaderLine('Set-Cookie')
        );
    }

    /**
     * testPersistIdentityOtherField
     *
     * @return void
     */
    public function testPersistIdentityOtherField(): void
    {
        $response = $this->getMockResponse();
        $response->expects($this->once())
            ->method('getHeaderLine', 'Set-Cookie')
            ->willReturn('CookieAuth=%5B%22robert%22%2C%22%242y%2410%241bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS%5C%2FKGmC1hNuWkUG7ES%22%5D; path=/');

        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('write')
            ->willReturn($response);

        $authenticator = $this->createAuthenticator($storage);
        $authenticator->setRememberMeField('other_field');

        $request = $this->getMockRequest([
            'parsedBody' => [
                'other_field' => 1
            ]
        ]);

        $identity = $this->getIdentity();

        $result = $authenticator->persistIdentity($request, $response, $identity);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertStringContainsString('CookieAuth=%5B%22robert%22%2C%22%242y%2410%24', $result->getHeaderLine('Set-Cookie'));
    }

    /**
     * testPersistIdentityNoField
     *
     * @return void
     */
    public function testPersistIdentityNoField()
    {
        $response = new Response();
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('write')
            ->willReturn($response->withHeader(
                'Set-Cookie',
                'CookieAuth=%5B%22robert%22%2C%22%242y%2410%241bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS%5C%2FKGmC1hNuWkUG7ES%22%5D; path=/'
            ));
        $authenticator = $this->createAuthenticator($storage);

        $request = $this->getMockRequest([
            'path' => '/users/login',
        ]);

        $identity = $this->getIdentity();

        $result = $authenticator->persistIdentity($request, $response, $identity);

        $this->assertStringNotContainsString('CookieAuth', $result->getHeaderLine('Set-Cookie'));
    }

    /**
     * testPersistIdentityLoginUrlMismatch
     *
     * @return void
     */
    public function testPersistIdentityLoginUrlMismatch()
    {
        $storage = $this->createMock(StorageInterface::class);
        $authenticator = $this->createAuthenticator($storage);
        $authenticator->addLoginUrl('/users/login');

        $request = (new Psr17Factory())->createServerRequest('GET', '/invalid-url');
        $response = new Response();

        $identity = $this->getIdentity();

        $result = $authenticator->persistIdentity($request, $response, $identity);

        $this->assertStringNotContainsString(
            'CookieAuth=%5B%22robert%22%2C%22%242y%2410%24',
            $result->getHeaderLine('Set-Cookie')
        );
    }

    /**
     * testClearIdentity
     *
     * @return void
     */
    public function testClearIdentity()
    {
        $response = new Response();
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('clear')
            ->willReturn($response->withHeader('Set-Cookie', 'CookieAuth=; expires=Thu, 01-Jan-1970 00:00:01 UTC; path=/'));
        $authenticator = $this->createAuthenticator($storage);

        $request = (new Psr17Factory())->createServerRequest('GET', '/testpath');

        $result = $authenticator->clearIdentity($request, $response);
        $this->assertInstanceOf(ResponseInterface::class, $result);

        $this->assertEquals('CookieAuth=; expires=Thu, 01-Jan-1970 00:00:01 UTC; path=/', $result->getHeaderLine('Set-Cookie'));
    }
}

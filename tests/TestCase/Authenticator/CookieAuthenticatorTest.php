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

use ArrayObject;
use Authentication\Authenticator\CookieAuthenticator;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\Storage\CakeCookieStorage;
use Authentication\Authenticator\Storage\StorageInterface;
use Authentication\Identifier\PasswordIdentifier;
use Authentication\Identifier\Resolver\OrmResolver;
use Phauthentic\PasswordHasher\DefaultPasswordHasher;
use Authentication\UrlChecker\DefaultUrlChecker;
use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\ResponseInterface;

class CookieAuthenticatorTest extends TestCase
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
     * @param StorageInterface $storage Storage instance.
     * @return CookieAuthenticator
     */
    protected function createAuthenticator(StorageInterface $storage)
    {
        $hasher = new DefaultPasswordHasher();
        $identifiers = new PasswordIdentifier(new OrmResolver(), $hasher);

        return new CookieAuthenticator($identifiers, $storage, $hasher, new DefaultUrlChecker());
    }

    /**
     * testAuthenticateInvalidTokenMissingUsername
     *
     * @return void
     */
    public function testAuthenticateInvalidTokenMissingUsername()
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('read')
            ->willReturn(["$2y$10$1bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS/KGmC1hNuWkUG7ES"]);

        $authenticator = $this->createAuthenticator($storage);

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath']
        );
        $response = new Response();

        $result = $authenticator->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_INVALID, $result->getStatus());
    }

    /**
     * testAuthenticateSuccess
     *
     * @return void
     */
    public function testAuthenticateSuccess()
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('read')
            ->willReturn(["mariano","$2y$10$1bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS/KGmC1hNuWkUG7ES"]);
        $authenticator = $this->createAuthenticator($storage);

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath']
        );
        $response = new Response();

        $result = $authenticator->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
    }

    /**
     * testAuthenticateUnknownUser
     *
     * @return void
     */
    public function testAuthenticateUnknownUser()
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('read')
            ->willReturn(["robert","$2y$10$1bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS/KGmC1hNuWkUG7ES"]);
        $authenticator = $this->createAuthenticator($storage);

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath']
        );
        $response = new Response();

        $result = $authenticator->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getStatus());
    }

    /**
     * testCredentialsNotPresent
     *
     * @return void
     */
    public function testCredentialsNotPresent()
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('read')
            ->willReturn(null);
        $authenticator = $this->createAuthenticator($storage);

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath']
        );
        $response = new Response();

        $result = $authenticator->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_MISSING, $result->getStatus());
    }

    /**
     * testAuthenticateInvalidToken
     *
     * @return void
     */
    public function testAuthenticateInvalidToken()
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('read')
            ->willReturn(["mariano","$2y$10$1bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS/asdasdsadasd"]);
        $authenticator = $this->createAuthenticator($storage);

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath']
        );
        $response = new Response();

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
        $response = new Response();
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('write')
            ->willReturn($response->withHeader(
                'Set-Cookie',
                'CookieAuth=%5B%22mariano%22%2C%22%242y%2410%241bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS%5C%2FKGmC1hNuWkUG7ES%22%5D; path=/'
            ));
        $authenticator = $this->createAuthenticator($storage);

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath']
        )->withParsedBody([
            'remember_me' => 1
        ]);

        $identity = new ArrayObject([
            'username' => 'mariano',
            'password' => '$2a$10$u05j8FjsvLBNdfhBhc21LOuVMpzpabVXQ9OpC2wO3pSO0q6t7HHMO'
        ]);
        $result = $authenticator->persistIdentity($request, $response, $identity);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertContains('CookieAuth=%5B%22mariano%22%2C%22%242y%2410%24', $result->getHeaderLine('Set-Cookie'));
    }

    /**
     * testPersistIdentityOtherField
     *
     * @return void
     */
    public function testPersistIdentityOtherField()
    {
        $response = new Response();
        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('write')
            ->willReturn($response->withHeader(
                'Set-Cookie',
                'CookieAuth=%5B%22mariano%22%2C%22%242y%2410%241bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS%5C%2FKGmC1hNuWkUG7ES%22%5D; path=/'
            ));
        $authenticator = $this->createAuthenticator($storage);
        $authenticator->setRememberMeField('other_field');

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath']
        )->withParsedBody([
            'other_field' => 1
        ]);

        $identity = new ArrayObject([
            'username' => 'mariano',
            'password' => '$2a$10$u05j8FjsvLBNdfhBhc21LOuVMpzpabVXQ9OpC2wO3pSO0q6t7HHMO'
        ]);

        $result = $authenticator->persistIdentity($request, $response, $identity);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertContains('CookieAuth=%5B%22mariano%22%2C%22%242y%2410%24', $result->getHeaderLine('Set-Cookie'));
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
                'CookieAuth=%5B%22mariano%22%2C%22%242y%2410%241bE1SgasKoz9WmEvUfuZLeYa6pQgxUIJ5LAoS%5C%2FKGmC1hNuWkUG7ES%22%5D; path=/'
            ));
        $authenticator = $this->createAuthenticator($storage);

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath']
        );

        $identity = new ArrayObject([
            'username' => 'mariano',
            'password' => '$2a$10$u05j8FjsvLBNdfhBhc21LOuVMpzpabVXQ9OpC2wO3pSO0q6t7HHMO'
        ]);
        $result = $authenticator->persistIdentity($request, $response, $identity);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertNotContains('CookieAuth', $result->getHeaderLine('Set-Cookie'));
    }

    /**
     * testPersistIdentityLoginUrlMismatch
     *
     * @return void
     */
    public function testPersistIdentityLoginUrlMismatch()
    {
        $storage = new CakeCookieStorage();
        $authenticator = $this->createAuthenticator($storage);
        $authenticator->addLoginUrl('/users/login');

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath']
        );
        $request = $request->withParsedBody([
            'remember_me' => 1
        ]);
        $response = new Response();

        $identity = new ArrayObject([
            'username' => 'mariano',
            'password' => '$2a$10$u05j8FjsvLBNdfhBhc21LOuVMpzpabVXQ9OpC2wO3pSO0q6t7HHMO'
        ]);
        $result = $authenticator->persistIdentity($request, $response, $identity);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertNotContains('CookieAuth=%5B%22mariano%22%2C%22%242y%2410%24', $result->getHeaderLine('Set-Cookie'));
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

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath']
        );

        $result = $authenticator->clearIdentity($request, $response);
        $this->assertInstanceOf(ResponseInterface::class, $result);

        $this->assertEquals('CookieAuth=; expires=Thu, 01-Jan-1970 00:00:01 UTC; path=/', $result->getHeaderLine('Set-Cookie'));
    }
}

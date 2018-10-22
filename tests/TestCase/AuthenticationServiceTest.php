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
use Phauthentic\Authentication\AuthenticationService;
use Phauthentic\Authentication\Authenticator\AuthenticatorCollection;
use Phauthentic\Authentication\Authenticator\Exception\UnauthorizedException;
use Phauthentic\Authentication\Authenticator\FailureInterface;
use Phauthentic\Authentication\Authenticator\FormAuthenticator;
use Phauthentic\Authentication\Authenticator\HttpBasicAuthenticator;
use Phauthentic\Authentication\Authenticator\ResultInterface;
use Phauthentic\Authentication\Authenticator\SessionAuthenticator;
use Phauthentic\Authentication\Authenticator\Storage\StorageInterface;
use Phauthentic\Authentication\Identifier\IdentifierInterface;
use Phauthentic\Authentication\Identifier\PasswordIdentifier;
use Phauthentic\Authentication\Identity\DefaultIdentityFactory;
use Phauthentic\Authentication\Identity\Identity;
use Phauthentic\Authentication\Identity\IdentityFactoryInterface;
use Phauthentic\Authentication\PersistenceResultInterface;
use Phauthentic\Authentication\Test\Resolver\TestResolver;
use Phauthentic\Authentication\Test\TestCase\AuthenticationTestCase as TestCase;
use Phauthentic\Authentication\UrlChecker\DefaultUrlChecker;
use Phauthentic\Authentication\UrlChecker\UrlCheckerInterface;
use Phauthentic\PasswordHasher\DefaultPasswordHasher;
use RuntimeException;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class AuthenticationServiceTest extends TestCase
{
    protected function createPasswordIdentifier()
    {
        $resolver = new TestResolver($this->getConnection()->getConnection());
        $passwordHasher = new DefaultPasswordHasher();

        return new PasswordIdentifier($resolver, $passwordHasher);
    }

    protected function createSessionAuthenticator(IdentifierInterface $identifier = null, StorageInterface $storage = null)
    {
        if (!$identifier) {
            $identifier = $this->createPasswordIdentifier();
        }
        if (!$storage) {
            $storage = $this->createMock(StorageInterface::class);
        }

        return new SessionAuthenticator($identifier, $storage);
    }

    protected function createFormAuthenticator(IdentifierInterface $identifier = null, UrlCheckerInterface $urlChecker = null)
    {
        if (!$identifier) {
            $identifier = $this->createPasswordIdentifier();
        }

        if (!$urlChecker) {
            $urlChecker = new DefaultUrlChecker();
        }

        return new FormAuthenticator($identifier, $urlChecker);
    }

    protected function createAuthenticators(IdentifierInterface $identifier = null, StorageInterface $storage = null)
    {
        $authenticators = new AuthenticatorCollection();

        if (!$identifier) {
            $identifier = $this->createPasswordIdentifier();
        }
        $authenticators->add($this->createSessionAuthenticator($identifier, $storage));
        $authenticators->add($this->createFormAuthenticator($identifier));

        return $authenticators;
    }

    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticate()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'robert', 'password' => 'robert']
        );

        $authenticators = $this->createAuthenticators();
        $service = new AuthenticationService($authenticators, new DefaultIdentityFactory);

        $success = $service->authenticate($request);
        $this->assertTrue($success);

        $result = $service->getResult();
        $this->assertTrue($result->isValid());

        $auth = $service->getSuccessfulAuthenticator();
        $this->assertInstanceOf(FormAuthenticator::class, $auth);

        $identity = $service->getIdentity();
        $this->assertEquals('robert', $identity['username']);

        $failures = $service->getFailures();
        $this->assertCount(1, $failures);

        $this->assertArrayHasKey(0, $failures);
        $this->assertInstanceOf(FailureInterface::class, $failures[0]);
        $this->assertInstanceOf(SessionAuthenticator::class, $failures[0]->getAuthenticator());
        $this->assertInstanceOf(ResultInterface::class, $failures[0]->getResult());
        $this->assertFalse($failures[0]->getResult()->isValid());
    }

    /**
     * testAuthenticateFailure
     *
     * @return void
     */
    public function testAuthenticateFailure()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'robert', 'password' => 'invalid']
        );

        $authenticators = $this->createAuthenticators();
        $service = new AuthenticationService($authenticators, new DefaultIdentityFactory);

        $success = $service->authenticate($request);
        $this->assertFalse($success);

        $result = $service->getResult();
        $this->assertFalse($result->isValid());

        $auth = $service->getSuccessfulAuthenticator();
        $this->assertNull($auth);

        $identity = $service->getIdentity();
        $this->assertNull($identity);

        $failures = $service->getFailures();
        $this->assertCount(2, $failures);

        $this->assertArrayHasKey(0, $failures);
        $this->assertInstanceOf(FailureInterface::class, $failures[0]);
        $this->assertInstanceOf(SessionAuthenticator::class, $failures[0]->getAuthenticator());
        $this->assertInstanceOf(ResultInterface::class, $failures[0]->getResult());
        $this->assertFalse($failures[0]->getResult()->isValid());

        $this->assertArrayHasKey(1, $failures);
        $this->assertInstanceOf(FailureInterface::class, $failures[1]);
        $this->assertInstanceOf(FormAuthenticator::class, $failures[1]->getAuthenticator());
        $this->assertInstanceOf(ResultInterface::class, $failures[1]->getResult());
        $this->assertFalse($failures[1]->getResult()->isValid());
    }

    /**
     * testAuthenticateStorage
     *
     * @return void
     */
    public function testAuthenticateStorage()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath']
        );

        $storage = $this->createMock(StorageInterface::class);
        $identity = new Identity(new ArrayObject(['username' => 'robert']));
        $storage
            ->expects($this->once())
            ->method('read')
            ->with($request)
            ->willReturn($identity);

        $authenticators = new AuthenticatorCollection([
            $this->createFormAuthenticator(),
            $this->createSessionAuthenticator(null, $storage),
        ]);
        $service = new AuthenticationService($authenticators, new DefaultIdentityFactory);

        $success = $service->authenticate($request);
        $this->assertTrue($success);

        $result = $service->getResult();
        $this->assertTrue($result->isValid());

        $auth = $service->getSuccessfulAuthenticator();
        $this->assertInstanceOf(SessionAuthenticator::class, $auth);

        $identity = $service->getIdentity();
        $this->assertEquals('robert', $identity['username']);

        $failures = $service->getFailures();
        $this->assertCount(1, $failures);

        $this->assertArrayHasKey(0, $failures);
        $this->assertInstanceOf(FailureInterface::class, $failures[0]);
        $this->assertInstanceOf(FormAuthenticator::class, $failures[0]->getAuthenticator());
        $this->assertInstanceOf(ResultInterface::class, $failures[0]->getResult());
        $this->assertFalse($failures[0]->getResult()->isValid());
    }

    /**
     * test authenticate() with a challenger authenticator
     *
     * @return void
     */
    public function testAuthenticateWithChallenge()
    {
        $request = ServerRequestFactory::fromGlobals([
            'SERVER_NAME' => 'example.com',
            'REQUEST_URI' => '/testpath',
            'PHP_AUTH_USER' => 'robert',
            'PHP_AUTH_PW' => 'WRONG'
        ]);

        $identifier = $this->createPasswordIdentifier();
        $authenticators = new AuthenticatorCollection([
            new HttpBasicAuthenticator($identifier),
        ]);
        $service = new AuthenticationService($authenticators, new DefaultIdentityFactory);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionCode(401);

        $service->authenticate($request);
    }

    /**
     * testPersistAuthenticatedIdentity
     *
     * @return void
     */
    public function testPersistAuthenticatedIdentity()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'robert', 'password' => 'robert']
        );
        $response = new Response();

        $storage = $this->createMock(StorageInterface::class);

        $authenticators = $this->createAuthenticators(null, $storage);
        $service = new AuthenticationService($authenticators, new DefaultIdentityFactory);

        $success = $service->authenticate($request);
        $this->assertTrue($success);

        $result = $service->getResult();
        $this->assertTrue($result->isValid());

        $auth = $service->getSuccessfulAuthenticator();
        $this->assertInstanceOf(FormAuthenticator::class, $auth);

        $storage
            ->expects($this->once())
            ->method('write')
            ->with($request, $response, $service->getIdentity()->getOriginalData())
            ->willReturn($response->withHeader('Identity', 'Stored'));

        $result = $service->persistIdentity($request, $response);
        $this->assertInstanceOf(PersistenceResultInterface::class, $result);
        $this->assertEquals('Stored', $result->getResponse()->getHeaderLine('Identity'));
    }

    /**
     * testPersistCustomIdentity
     *
     * @return void
     */
    public function testPersistCustomIdentity()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'robert', 'password' => 'robert']
        );
        $response = new Response();

        $storage = $this->createMock(StorageInterface::class);

        $authenticators = $this->createAuthenticators(null, $storage);
        $service = new AuthenticationService($authenticators, new DefaultIdentityFactory);

        $identity = new Identity(new ArrayObject(['username' => 'robert']));

        $storage
            ->expects($this->once())
            ->method('write')
            ->with($request, $response, $identity->getOriginalData())
            ->willReturn($response->withHeader('Identity', 'Stored'));

        $result = $service->persistIdentity($request, $response, $identity);
        $this->assertInstanceOf(PersistenceResultInterface::class, $result);
        $this->assertEquals('Stored', $result->getResponse()->getHeaderLine('Identity'));
    }

    /**
     * testClearIdentity
     *
     * @return void
     */
    public function testClearIdentity()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'robert', 'password' => 'robert']
        );
        $response = new Response();

        $storage = $this->createMock(StorageInterface::class);

        $authenticators = $this->createAuthenticators(null, $storage);
        $service = new AuthenticationService($authenticators, new DefaultIdentityFactory);

        $storage
            ->expects($this->once())
            ->method('clear')
            ->with($request, $response)
            ->willReturn($response->withHeader('Identity', 'Cleared'));

        $result = $service->clearIdentity($request, $response);
        $this->assertInstanceOf(PersistenceResultInterface::class, $result);
        $this->assertEquals('Cleared', $result->getResponse()->getHeaderLine('Identity'));
        $this->assertNull($service->getIdentity());
        $this->assertNull($service->getResult());
        $this->assertNull($service->getSuccessfulAuthenticator());
        $this->assertEmpty($service->getFailures());
    }

    /**
     * testNoAuthenticatorsLoadedException
     *
     * @return void
     */
    public function testNoAuthenticatorsLoadedException()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'robert', 'password' => 'robert']
        );

        $service = new AuthenticationService(new AuthenticatorCollection(), new DefaultIdentityFactory);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No authenticators loaded. You need to load at least one authenticator.');

        $service->authenticate($request);
    }

    /**
     * testBuildIdentity
     *
     * @return void
     */
    public function testBuildIdentity()
    {
        $data = new ArrayObject(['username' => 'robert']);
        $identity = new Identity($data);
        $factory = $this->createMock(IdentityFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('create')
            ->with($data)
            ->willReturn($identity);

        $service = new AuthenticationService($this->createAuthenticators(), $factory);

        $result = $service->buildIdentity($data);
        $this->assertSame($identity, $result);
    }

    public function testGetIdentity()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'robert', 'password' => 'robert']
        );

        $authenticators = $this->createAuthenticators();
        $service = new AuthenticationService($authenticators, new DefaultIdentityFactory);

        $success = $service->authenticate($request);
        $this->assertTrue($success);

        $identity = $service->getIdentity();
        $this->assertNotNull($identity);

        $this->assertSame($identity, $service->getIdentity());
    }
}

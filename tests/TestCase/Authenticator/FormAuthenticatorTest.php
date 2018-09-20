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
use Phauthentic\Authentication\Authenticator\FormAuthenticator;
use Phauthentic\Authentication\Authenticator\Result;
use Phauthentic\Authentication\Identifier\IdentifierInterface;
use Phauthentic\Authentication\Identifier\PasswordIdentifier;
use Phauthentic\Authentication\Test\Resolver\TestResolver;
use Phauthentic\Authentication\Test\TestCase\AuthenticationTestCase as TestCase;
use Phauthentic\Authentication\UrlChecker\DefaultUrlChecker;
use Phauthentic\Authentication\UrlChecker\RegexUrlChecker;
use Phauthentic\PasswordHasher\DefaultPasswordHasher;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

/**
 * FormAuthenticatorTest
 */
class FormAuthenticatorTest extends TestCase
{
    protected function getIdentifier(): IdentifierInterface
    {
        $resolver = new TestResolver($this->getConnection()->getConnection());

        return new PasswordIdentifier($resolver, new DefaultPasswordHasher());
    }

    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticate()
    {
        $identifier = $this->getIdentifier();
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'florian', 'password' => 'florian']
        );
        $response = new Response();

        $form = new FormAuthenticator($identifier, new DefaultUrlChecker());
        $result = $form->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
    }

    /**
     * testCredentialsNotPresent
     *
     * @return void
     */
    public function testCredentialsNotPresent()
    {
        $identifier = $this->getIdentifier();

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/users/does-not-match'],
            [],
            []
        );
        $response = new Response();

        $urlChecker = new DefaultUrlChecker();
        $form = new FormAuthenticator($identifier, $urlChecker);

        $result = $form->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_MISSING, $result->getStatus());
        $this->assertEquals([0 => 'Login credentials not found'], $result->getErrors());
    }

    /**
     * testCredentialsEmpty
     *
     * @return void
     */
    public function testCredentialsEmpty()
    {
        $identifier = $this->getIdentifier();

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/users/does-not-match'],
            [],
            ['username' => '', 'password' => '']
        );
        $response = new Response();

        $urlChecker = new DefaultUrlChecker();
        $form = new FormAuthenticator($identifier, $urlChecker);

        $result = $form->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_MISSING, $result->getStatus());
        $this->assertEquals([0 => 'Login credentials not found'], $result->getErrors());
    }

    /**
     * testSingleLoginUrlMismatch
     *
     * @return void
     */
    public function testSingleLoginUrlMismatch()
    {
        $identifier = $this->getIdentifier();

        $request = ServerRequestFactory::fromGlobals(
            [
                'REQUEST_URI' => '/users/does-not-match',
                'HTTP_HOST' => 'localhost',
            ],
            [],
            ['username' => 'florian', 'password' => 'florian']
        );
        $response = new Response();

        $urlChecker = new DefaultUrlChecker();
        $form = (new FormAuthenticator($identifier, $urlChecker))
            ->addLoginUrl('/users/login');

        $result = $form->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_OTHER, $result->getStatus());
        $this->assertEquals([0 => 'Login URL `http://localhost/users/does-not-match` did not match `/users/login`.'], $result->getErrors());
    }

    /**
     * testMultipleLoginUrlMismatch
     *
     * @return void
     */
    public function testMultipleLoginUrlMismatch()
    {
        $identifier = $this->getIdentifier();

        $request = ServerRequestFactory::fromGlobals(
            [
                'REQUEST_URI' => '/users/does-not-match',
                'HTTP_HOST' => 'localhost',
            ],
            [],
            ['username' => 'florian', 'password' => 'florian']
        );
        $response = new Response();

        $urlChecker = new DefaultUrlChecker();
        $form = (new FormAuthenticator($identifier, $urlChecker))
            ->setLoginUrls([
                '/en/users/login',
                '/de/users/login'
            ]);

        $result = $form->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_OTHER, $result->getStatus());
        $this->assertEquals([0 => 'Login URL `http://localhost/users/does-not-match` did not match `/en/users/login` or `/de/users/login`.'], $result->getErrors());
    }

    /**
     * testSingleLoginUrlSuccess
     *
     * @return void
     */
    public function testSingleLoginUrlSuccess()
    {
        $identifier = $this->getIdentifier();

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/Users/login'],
            [],
            ['username' => 'florian', 'password' => 'florian']
        );
        $response = new Response();

        $urlChecker = new DefaultUrlChecker();
        $form = (new FormAuthenticator($identifier, $urlChecker))
            ->addLoginUrl('/Users/login');

        $result = $form->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
        $this->assertEquals([], $result->getErrors());
    }

    /**
     * testMultipleLoginUrlSuccess
     *
     * @return void
     */
    public function testMultipleLoginUrlSuccess()
    {
        $identifier = $this->getIdentifier();

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/de/users/login'],
            [],
            ['username' => 'florian', 'password' => 'florian']
        );
        $response = new Response();

        $urlChecker = new DefaultUrlChecker();
        $form = (new FormAuthenticator($identifier, $urlChecker))
            ->setLoginUrls([
                '/en/users/login',
                '/de/users/login'
            ]);

        $result = $form->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
        $this->assertEquals([], $result->getErrors());
    }

    /**
     * testRegexLoginUrlSuccess
     *
     * @return void
     */
    public function testRegexLoginUrlSuccess()
    {
        $identifier = $this->getIdentifier();

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/de/users/login'],
            [],
            ['username' => 'florian', 'password' => 'florian']
        );
        $response = new Response();

        $urlChecker = (new RegexUrlChecker());

        $form = (new FormAuthenticator($identifier, $urlChecker))
            ->addLoginUrl('%^/[a-z]{2}/users/login/?$%');

        $result = $form->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
        $this->assertEquals([], $result->getErrors());
    }

    /**
     * testFullRegexLoginUrlFailure
     *
     * @return void
     */
    public function testFullRegexLoginUrlFailure()
    {
        $identifier = $this->getIdentifier();

        $request = ServerRequestFactory::fromGlobals(
            [
                'REQUEST_URI' => '/de/users/login',
                'HTTP_HOST' => 'localhost',
            ],
            [],
            ['username' => 'florian', 'password' => 'florian']
        );
        $response = new Response();

        $urlChecker = (new RegexUrlChecker())
            ->checkFullUrl(true);

        $form = (new FormAuthenticator($identifier, $urlChecker))
            ->addLoginUrl('%auth\.localhost/[a-z]{2}/users/login/?$%');

        $result = $form->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_OTHER, $result->getStatus());
        $this->assertEquals([0 => 'Login URL `http://localhost/de/users/login` did not match `%auth\.localhost/[a-z]{2}/users/login/?$%`.'], $result->getErrors());
    }

    /**
     * testRegexLoginUrlSuccess
     *
     * @return void
     */
    public function testFullRegexLoginUrlSuccess()
    {
        $identifier = $this->getIdentifier();

        $request = ServerRequestFactory::fromGlobals(
            [
                'REQUEST_URI' => '/de/users/login',
                'SERVER_NAME' => 'auth.localhost'
            ],
            [],
            ['username' => 'florian', 'password' => 'florian']
        );
        $response = new Response();

        $urlChecker = (new RegexUrlChecker())
            ->checkFullUrl(true);

        $form = (new FormAuthenticator($identifier, $urlChecker))
            ->addLoginUrl('%auth\.localhost/[a-z]{2}/users/login/?$%');

        $result = $form->authenticate($request, $response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
        $this->assertEquals([], $result->getErrors());
    }

    /**
     * testAuthenticateCustomFields
     *
     * @return void
     */
    public function testAuthenticateCustomFields()
    {
        $identifier = $this->createMock(IdentifierInterface::class);

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/users/login'],
            [],
            ['email' => 'florian@cakephp.org', 'secret' => 'florian']
        );
        $response = new Response();

        $form = (new FormAuthenticator($identifier, new DefaultUrlChecker()))
            ->addLoginUrl('/users/login')
            ->setCredentialFields('email', 'secret');

        $identifier->expects($this->once())
            ->method('identify')
            ->with([
                'username' => 'florian@cakephp.org',
                'password' => 'florian'
            ])
            ->willReturn(new ArrayObject([
                'username' => 'florian@cakephp.org',
                'password' => 'florian'
            ]));

        $form->authenticate($request, $response);
    }

    /**
     * testAuthenticateValidData
     *
     * @return void
     */
    public function testAuthenticateValidData()
    {
        $identifier = $this->createMock(IdentifierInterface::class);

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/users/login'],
            [],
            ['id' => 1, 'username' => 'florian', 'password' => 'florian']
        );
        $response = new Response();

        $form = (new FormAuthenticator($identifier, new DefaultUrlChecker()))
            ->addLoginUrl('/users/login');

        $identifier->expects($this->once())
            ->method('identify')
            ->with([
                'username' => 'florian',
                'password' => 'florian'
            ])
            ->willReturn(new ArrayObject([
                'username' => 'florian',
                'password' => 'florian'
            ]));

        $form->authenticate($request, $response);
    }
}

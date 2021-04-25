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
namespace Phauthentic\Authentication\Test\TestCase\Middleware;

use ArrayObject;
use Phauthentic\Authentication\AuthenticationServiceInterface;
use Phauthentic\Authentication\AuthenticationServiceProviderInterface;
use Phauthentic\Authentication\Identity\Identity;
use Phauthentic\Authentication\Middleware\AuthenticationMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Authentication Middleware Test
 */
class AuthenticationMiddlewareTest extends TestCase
{

    use HttpEnvMockTrait;

    /**
     * The :void return type declaration that should be here would cause a BC issue
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setupPsrHttpMessageTraits();
    }

    /**
     * testProcess
     *
     * @return void
     */
    public function testProcess(): void
    {
        $this->request->expects($this->atLeastOnce())
            ->method('withAttribute')
            ->willReturnSelf();

        $service = $this
            ->getMockBuilder(AuthenticationServiceInterface::class)
            ->getMock();

        $service->expects($this->atLeastOnce())
            ->method('authenticate');

        $service->expects($this->atLeastOnce())
            ->method('getIdentity')
            ->willReturn(new Identity(new ArrayObject(['id' => 1, 'username' => 'florian'])));

        $serviceProvider = $this
            ->getMockBuilder(AuthenticationServiceProviderInterface::class)
            ->getMock();

        $serviceProvider->expects($this->atLeastOnce())
            ->method('getAuthenticationService')
            ->willReturn($service);

        $requestHandler =  $this
            ->getMockBuilder(RequestHandlerInterface::class)
            ->getMock();

        $middleware = new AuthenticationMiddleware(
            $serviceProvider
        );

        $result = $middleware->process($this->request, $requestHandler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
}

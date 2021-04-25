<?php
/**
 * Copyright (c) Phauthentic (https://github.com/Phauthentic)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Phauthentic (https://github.com/Phauthentic)
 * @link          https://github.com/Phauthentic
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Phauthentic\Authentication\Test\TestCase\Middleware;

use Phauthentic\Authentication\AuthenticationServiceProviderInterface;
use Phauthentic\Authentication\Authenticator\Exception\UnauthenticatedException;
use Phauthentic\Authentication\Authenticator\Exception\UnauthorizedException;
use Phauthentic\Authentication\Middleware\AuthenticationErrorHandlerMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
class TestRequestHandler implements RequestHandlerInterface
{
    /**
     * Exception
     *
     * @var \Throwable
     */
    protected $exception;

    /**
     * Constructor
     *
     * @param \Throwable $exception Exception
     */
    public function __construct($exception)
    {
        $this->exception = $exception;
    }

    /**
     * Handle the request and return a response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw $this->exception;
    }
}

/**
 * Authentication Error Handler Middleware Test
 */
class AuthenticationErrorHandlerMiddlewareTest extends TestCase
{
    use HttpEnvMockTrait;

    /**
     * The :void return type declaration that should be here would cause a BC issue
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->request = $this
            ->getMockBuilder(ServerRequestInterface::class)
            ->getMock();

        $this->response = $this
            ->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $this->responseFactory = $this
            ->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();

        $this->streamFactory = $this
            ->getMockBuilder(StreamFactoryInterface::class)
            ->getMock();

        $this->stream = $this
            ->getMockBuilder(StreamInterface::class)
            ->getMock();
    }

    /**
     * testProcessAndCreateErrorResponse
     *
     * @return void
     */
    public function testProcessAndCreateErrorResponse(): void
    {
        $handler = new TestRequestHandler(new UnauthenticatedException('Failed to authenticate :('));
        $middleware = new AuthenticationErrorHandlerMiddleware($this->responseFactory, $this->streamFactory);

        $this->expectException(UnauthenticatedException::class);
        $middleware->process($this->request, $handler);
    }

    /**
     * testProcessAndCreateUnauthorizedResponse
     *
     * @return void
     */
    public function testProcessAndCreateUnauthorizedResponse(): void
    {
        $handler = new TestRequestHandler(new UnauthorizedException([], 'Failed :('));
        $middleware = new AuthenticationErrorHandlerMiddleware($this->responseFactory, $this->streamFactory);

        $this->stream->expects($this->once())
            ->method('write')
            ->with('Failed :(');

        $this->streamFactory->expects($this->any())
            ->method('createStream')
            ->willReturn($this->stream);

        $this->response->expects($this->once())
            ->method('withBody')
            ->willReturnSelf();

        $this->responseFactory->expects($this->any())
            ->method('createResponse')
            ->willReturn($this->response);

        $middleware->process($this->request, $handler);
    }
}

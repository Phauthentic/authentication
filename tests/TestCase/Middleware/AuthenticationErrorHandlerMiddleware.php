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
use Phauthentic\Authentication\HttpFactory\ZendDiactoresResponseFactory;
use Phauthentic\Authentication\HttpFactory\ZendStreamFactory;
use Phauthentic\Authentication\Middleware\AuthenticationErrorHandlerMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TestRequestHandler implements RequestHandlerInterface {

    /**
     * Handle the request and return a response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new UnauthorizedException([], 'Failed :(');
    }
}

/**
 * Authentication Error Handler Middleware Test
 */
class AuthenticationErrorHandlerMiddlewareTest extends TestCase
{
    /**
     * testProcessAndCreateUnauthorizedResponse
     *
     * @return void
     */
    public function testProcessAndCreateUnauthorizedResponse(): void {
        $request = $this
            ->getMockBuilder(ServerRequestInterface::class)
            ->getMock();

        $response = $this
            ->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $responseFactory = $this
            ->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();

        $streamFactory = $this
            ->getMockBuilder(StreamFactoryInterface::class)
            ->getMock();

        $stream = $this
            ->getMockBuilder(StreamInterface::class)
            ->getMock();

        $handler = new TestRequestHandler();
        $middleware = new AuthenticationErrorHandlerMiddleware($responseFactory, $streamFactory);

        $stream->expects($this->once())
            ->method('write')
            ->with('Failed :(');

        $streamFactory->expects($this->any())
            ->method('createStream')
            ->willReturn($stream);

        $request->expects($this->once())
            ->method('withBody')
            ->willReturn($response);

        $responseFactory->expects($this->any())
            ->method('createResponse')
            ->willReturn($response);

        $middleware->process($request, $handler);
    }
}

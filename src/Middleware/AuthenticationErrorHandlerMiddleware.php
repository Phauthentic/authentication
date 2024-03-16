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

declare(strict_types=1);

namespace Phauthentic\Authentication\Middleware;

use Phauthentic\Authentication\Authenticator\Exception\UnauthorizedException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Handles the case when the authentication middleware has thrown an exception
 */
class AuthenticationErrorHandlerMiddleware implements MiddlewareInterface
{
    /**
     * Response factory
     *
     * @var \Psr\Http\Message\ResponseFactoryInterface
     */
    protected ResponseFactoryInterface $responseFactory;

    /**
     * PSR Stream Interface
     *
     * @var \Psr\Http\Message\StreamFactoryInterface
     */
    protected StreamFactoryInterface $streamFactory;

    /**
     * Constructor.
     *
     * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory Factory.
     * @param \Psr\Http\Message\StreamFactoryInterface $streamFactory Factory.
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (UnauthorizedException $e) {
            return $this->createUnauthorizedResponse($e);
        }
    }

    /**
     * Creates an unauthorized response.
     *
     * @param \Phauthentic\Authentication\Authenticator\Exception\UnauthorizedException $e Exception.
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function createUnauthorizedResponse(UnauthorizedException $exception): ResponseInterface
    {
        $body = $this->streamFactory->createStream();
        $body->write($exception->getBody());

        $response = $this
            ->responseFactory
            ->createResponse($exception->getCode())
            ->withBody($body);

        foreach ($exception->getHeaders() as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        return $response;
    }
}

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
use Phauthentic\Authentication\HttpFactory\ZendDiactoresResponseFactory;
use Phauthentic\Authentication\HttpFactory\ZendStreamFactory;
use Phauthentic\Authentication\Middleware\AuthenticationMiddleware;
use PHPUnit\Framework\TestCase;

/**
 * Authentication Middleware Test
 */
trait HttpEnvMockTrait
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @param \Psr\Http\Message\ServerRequestInterface
     */
    protected $response;

    /**
     * @param \Psr\Http\Message\ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * @param \Psr\Http\Message\StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @param \Psr\Http\Message\StreamInterface
     */
    protected $stream;

    /**
     * Sets mocks up for the PSR interfaces
     *
     * @return void
     */
    public function setupPsrHttpMessageTraits(): void
    {
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
}

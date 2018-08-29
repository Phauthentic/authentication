<?php
namespace Authentication\Middleware;

use Authentication\Authenticator\AuthenticatorCollection;
use Authentication\Authenticator\AuthenticatorCollectionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PSR 15 Authenticator Middleware
 */
class AuthenticationPsr15Middleware implements MiddlewareInterface
{
    /**
     * Response factory
     *
     * @var null|\Psr\Http\Message\ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * Authenticator Collection
     *
     * @var \Authentication\Authenticator\AuthenticatorCollection
     */
    protected $authenticators;

    /**
     * AuthenticationPsr15Middleware constructor.
     *
     * @param \Authentication\Authenticator\AuthenticatorCollectionInterface $collection
     * @param null|\Psr\Http\Message\ResponseFactoryInterface $responseFactory
     */
    public function __construct(AuthenticatorCollectionInterface $collection, ?ResponseFactoryInterface $responseFactory = null)
    {
        $this->authenticators = $collection;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Process an incoming server request
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /* @var $authenticator \Authentication\Authenticator\AuthenticatorInterface */
        foreach ($this->authenticators as $authenticator) {
            $result = $authenticator->authenticate($request);
            if ($result->isValid()) {
                break;
            }
        }

        if (!empty($this->responseFactory)) {
            // @todo define the redirect somehow
            return $this->responseFactory->createResponse();
        }

        $request = $request->withAttribute('authentication', $result);

        return $handler->handle($request);
    }

    public function setResponseEmitter(ResponseFactoryInterface $emitter): ResponseInterface
    {
        $this->emitter;
    }
}

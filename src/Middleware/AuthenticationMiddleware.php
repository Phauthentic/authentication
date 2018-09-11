<?php
namespace Authentication\Middleware;

use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Authenticator\Exception\UnauthorizedException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Zend\Diactoros\Stream;

/**
 * PSR 15 Authenticator Middleware
 */
class AuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * Response factory
     *
     * @var \Psr\Http\Message\ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * @var \Authentication\AuthenticationServiceProviderInterface
     */
    protected $provider;

    /**
     * @var string
     */
    protected $serviceAttribute = 'authentication';

    /**
     * Constructor.
     *
     * @param \Authentication\AuthenticationServiceProviderInterface $provider Provider.
     * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory Factory.
     */
    public function __construct(
        AuthenticationServiceProviderInterface $provider,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->provider = $provider;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Sets request attribute name for authentication service.
     *
     * @param string $attribute Attribute name.
     * @return $this
     */
    public function setServiceAttribute(string $attribute): self
    {
        $this->serviceAttribute = $attribute;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException When request attribute exists.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $service = $this->provider->getAuthenticationService($request);

        try {
            $wasAuthenticated = $service->authenticate($request);
        } catch (UnauthorizedException $e) {
            return $this->createUnauthorizedResponse($e);
        }

        $authResult = $service->getResult();
        $authenticator = $service->getSuccessfulAuthenticator();

        if ($request->getAttribute($this->serviceAttribute)) {
            $message = sprintf('Request attribute `%s` already exists.', $this->serviceAttribute);
            throw new RuntimeException($message);
        }
        $request = $request->withAttribute($this->serviceAttribute, $authResult);

        $response = $handler->handle($request);
        if ($response instanceof ResponseInterface) {
            $result = $service->persistIdentity($request, $response);

            return $result->getResponse();
        }

        return $response;
    }

    /**
     * Creates an unauthorized response.
     *
     * @param UnauthorizedException $e Exception.
     * @return ResponseInterface
     */
    protected function createUnauthorizedResponse(UnauthorizedException $e): ResponseInterface
    {
        $body = new Stream('php://memory', 'rw');
        $body->write($e->getBody());
        $response = $this
            ->responseFactory
            ->createResponse($e->getCode())
            ->withBody($body);

        foreach ($e->getHeaders() as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        return $response;
    }
}

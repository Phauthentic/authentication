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
     * Request attribute for the identity
     *
     * @var string
     */
    protected $identityAttribute = 'identity';

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
     * Sets the identity attribute
     *
     * @param string $attribute Attribute name
     * @return $this
     */
    public function setIdentityAttribute(string $attribute): self
    {
        $this->identityAttribute = $attribute;

        return $this;
    }

    protected function addAttribute(ServerRequestInterface $request, string $name, $value): ServerRequestInterface
    {
        if ($request->getAttribute($name)) {
            $message = sprintf('Request attribute `%s` already exists.', $name);
            throw new RuntimeException($message);
        }

        return $request->withAttribute($name, $value);
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException When request attribute exists.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $service = $this->provider->getAuthenticationService($request);
        $request = $this->addAttribute($request, $this->serviceAttribute, $service);

        try {
            $service->authenticate($request);
        } catch (UnauthorizedException $e) {
            return $this->createUnauthorizedResponse($e);
        }

        $identity = $service->getIdentity();
        $request = $this->addAttribute($request, $this->identityAttribute, $identity);

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

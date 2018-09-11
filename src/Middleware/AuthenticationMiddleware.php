<?php
namespace Authentication\Middleware;

use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
     * Authentication failure redirect URL
     *
     * @var string|callable
     */
    protected $unauthorizedRedirectUrl;

    /**
     * Successful login redirect URL
     *
     * @var string|callable
     */
    protected $successRedirectUrl;

    /**
     * @var AuthenticationServiceInterface|null
     */
    protected $service;

    /**
     * @var AuthenticationServiceProviderInterface
     */
    protected $provider;

    /**
     * @var string
     */
    protected $serviceAttribute = 'authentication';

    /**
     * AuthenticationPsr15Middleware constructor.
     *
     * @param AuthenticationServiceProviderInterface $service
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(
        AuthenticationServiceProviderInterface $provider,
        ResponseFactoryInterface $responseFactory = null
    ){
        $this->provider = $provider;
        $this->responseFactory = $responseFactory;
    }

    public function setServiceAttribute(string $attribute): self
    {
        $this->serviceAttribute = $attribute;

        return $this;
    }

    protected function setRedirect($redirectUrl, $type)
    {
        if (!is_string($redirectUrl) && !is_callable($redirectUrl)) {
            throw new InvalidArgumentException('Redirect URL must be a string or callable');
        }

        if (!in_array($type, ['unauthorized', 'success'])) {
            throw new InvalidArgumentException('Type must be failure or success');
        }

        $property = $type . 'RedirectUrl';
        $this->{$property} = $redirectUrl;
    }

    public function setLoginRedirect($redirectUrl): self
    {
        $this->setRedirect($redirectUrl, 'success');

        return $this;
    }

    public function setUnauhtorizedRedirect($redirectUrl): self
    {
        $this->setRedirect($redirectUrl, 'unauthorized');

        return $this;
    }

    protected function getUnauthorizedRedirectResponse($request, $authResult, $authenticator): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(302);

        if (is_callable($this->failureDirectUrl)) {
            return $this->redirectUrl($request, $response, $authResult, $authenticator);
        }

        return $response->withHeader('Location', $this->redirectUrl);
    }

    /**
     *
     * @param ServerRequestInterface $request
     * @return AuthenticationServiceInterface
     */
    protected function getService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        if ($this->service === null) {
            $this->service = $this->provider->getAuthenticationService($request);
        }

        return $this->service;
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
        $service = $this->getService($request);
        $wasAuthenticated = $service->authenticate($request);

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

            return $result['response'];
        }

        return $response;
    }

    protected function getRedirectResponse(string $targetUrl): ResponseInterface
    {
        return $this->responseFactory
                ->createResponse(302)
                ->withHeader('Location', $targetUrl);
    }

    /**
     * Returns redirect URL.
     *
     * @param string $target Redirect target.
     * @param \Psr\Http\Message\ServerRequestInterface $request Request instance.
     * @return string
     */
    protected function getRedirectUrl(string $target, ServerRequestInterface $request): string
    {
        $param = $this->getConfig('queryParam');
        if ($param === null) {
            return $target;
        }

        $query = urlencode($param) . '=' . urlencode($request->getUri());
        if (strpos($target, '?') !== false) {
            $query = '&' . $query;
        } else {
            $query = '?' . $query;
        }

        return $target . $query;
    }
}

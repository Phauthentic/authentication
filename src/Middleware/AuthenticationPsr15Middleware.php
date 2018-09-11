<?php
namespace Authentication\Middleware;

use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\Authenticator\AuthenticatorCollection;
use Authentication\Authenticator\AuthenticatorCollectionInterface;
use Authentication\Authenticator\PersistenceInterface;
use Authentication\Authenticator\ResultInterface;
use Authentication\Authenticator\StatelessInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Translation\Exception\RuntimeException;

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
     * @var \Authentication\AuthenticationServiceInterface
     */
    protected $service;

    /**
     * AuthenticationPsr15Middleware constructor.
     *
     * @param \Authentication\Authenticator\AuthenticatorCollectionInterface $collection
     * @param null|\Psr\Http\Message\ResponseFactoryInterface $responseFactory
     */
    public function __construct(
        AuthenticationServiceInterface $service,
        AuthenticatorCollectionInterface $collection,
        ?ResponseFactoryInterface $responseFactory = null
    ){
        $this->service = $service;
        $this->authenticators = $collection;
        $this->responseFactory = $responseFactory;
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
        return $this;

        $this->setRedirect($redirectUrl, 'success');
    }

    public function setUnauhtorizedRedirect($redirectUrl): self
    {
        $this->setRedirect($redirectUrl, 'unauthorized');

        return $this;
    }

    protected function getUnauthorizedRedirectResponse($request, $authResult, $authenticator): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(301);

        if (is_callable($this->failureDirectUrl)) {
            return $this->redirectUrl($request, $response, $authResult, $authenticator);
        }

        return $response->withHeader('Location', $this->redirectUrl);
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
        $wasAuthenticated = $this->service->authenticate($request);

        $authResult = $this->service->getResult();
        $authenticator = $this->service->getSuccessfulAuthenticator();
        $request = $request->withAttribute('authentication', $authResult);

        if (!$wasAuthenticated) {
            if (!empty($this->responseFactory) && !empty($this->unauthorizedRedirectUrl)) {
                return $this->getUnauthorizedRedirectResponse($request, $authResult, $authenticator);
            }

            return $handler->handle($request);
        }

        if (!empty($this->responseFactory) && !empty($this->successRedirectUrl)) {
            $response = $this->getSuccessRedirectResponse($request, $authResult, $authenticator);
            $result = $this->service->persistIdentity($request, $response);

            return $response['response'];
        }

        $response = $handler->handle($request);
        if ($response instanceof ResponseInterface) {
            $result = $this->service->persistIdentity($request, $response);

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

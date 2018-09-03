<?php
namespace Authentication\Middleware;

use Authentication\Authenticator\AuthenticatorCollection;
use Authentication\Authenticator\AuthenticatorCollectionInterface;
use Authentication\Authenticator\PersistenceInterface;
use Authentication\Authenticator\StatelessInterface;
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

    protected $unauthorizedRedirectUrl;

    protected $successRedirectUrl;

    /**
     * AuthenticationPsr15Middleware constructor.
     *
     * @param \Authentication\Authenticator\AuthenticatorCollectionInterface $collection
     * @param null|\Psr\Http\Message\ResponseFactoryInterface $responseFactory
     */
    public function __construct(
        AuthenticatorCollectionInterface $collection,
        ?ResponseFactoryInterface $responseFactory = null
    ){
        $this->authenticators = $collection;
        $this->responseFactory = $responseFactory;
    }

    protected function setRedirect($redirectUrl, $type) {
        if (!is_string($redirectUrl) && !is_callable($redirectUrl)) {
            throw new \InvalidArgumentException('Redirect URL must be a string or callable');
        }

        if (!in_array($type, ['unauthorized', 'success'])) {
            throw new \InvalidArgumentException('Type must be failure or success');
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
        /* @var $authenticator \Authentication\Authenticator\AuthenticatorInterface */
        foreach ($this->authenticators as $authenticator) {
            $authResult = $authenticator->authenticate($request);
            if ($authResult->isValid()) {
                break;
            }
        }

        if (!$authResult->isValid() && $authenticator instanceof StatelessInterface) {
            $authenticator->unauthorizedChallenge($request);
        }

        if (!empty($this->responseFactory) && !empty($this->unauthorizedRedirectUrl)) {
            return $this->getUnauthorizedRedirectResponse($request, $authResult, $authenticator);
        }

        $request = $request->withAttribute('authentication', $authResult);

        $handlerResult = $handler->handle($request);

        if ($handlerResult instanceof ResponseInterface) {
            foreach ($this->authenticators as $authenticator) {
                if ($authenticator instanceof PersistenceInterface) {
                    //$authenticator->persistence()->save($identity);
                }
            }
        }

        return $handlerResult;
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

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

use Phauthentic\Authentication\AuthenticationServiceProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

/**
 * PSR 15 Authenticator Middleware
 */
class AuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * @var \Phauthentic\Authentication\AuthenticationServiceProviderInterface
     */
    protected AuthenticationServiceProviderInterface $provider;

    /**
     * @var string
     */
    protected string $serviceAttribute = 'authentication';

    /**
     * Request attribute for the identity
     *
     * @var string
     */
    protected string $identityAttribute = 'identity';

    /**
     * Constructor.
     *
     * @param \Phauthentic\Authentication\AuthenticationServiceProviderInterface $provider Provider.
     */
    public function __construct(
        AuthenticationServiceProviderInterface $provider
    ) {
        $this->provider = $provider;
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

    /**
     * Adds an attribute to the request and returns a modified request.
     *
     * @param ServerRequestInterface $request Request.
     * @param string $name Attribute name.
     * @param mixed $value Attribute value.
     * @return ServerRequestInterface
     * @throws RuntimeException When attribute is present.
     */
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

        $service->authenticate($request);

        $identity = $service->getIdentity();
        $request = $this->addAttribute($request, $this->identityAttribute, $identity);

        $response = $handler->handle($request);

        $result = $service->persistIdentity($request, $response);

        return $result->getResponse();
    }
}

<?php

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

declare(strict_types=1);

namespace Phauthentic\Authentication\Authenticator;

use ArrayAccess;
use Phauthentic\Authentication\Authenticator\Exception\UnauthorizedException;
use Phauthentic\Authentication\Identifier\IdentifierInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * HttpBasic Authenticator
 *
 * Provides Basic HTTP authentication support.
 */
class HttpBasicAuthenticator extends AbstractAuthenticator implements StatelessInterface
{
    use CredentialFieldsTrait;

    /**
     * Realm
     *
     * @var string|null
     */
    protected $realm;

    /**
     * Sets the realm
     *
     * @param string|null $realm Realm
     * @return $this
     */
    public function setRealm(?string $realm): self
    {
        $this->realm = $realm;

        return $this;
    }

    /**
     * Authenticate a user using HTTP auth. Will use the configured User model and attempt a
     * login using HTTP auth.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request to authenticate with.
     * @return \Phauthentic\Authentication\Authenticator\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request): ResultInterface
    {
        $user = $this->getUser($request);

        if (empty($user)) {
            return new Result(null, Result::FAILURE_CREDENTIALS_MISSING);
        }

        return new Result($user, Result::SUCCESS);
    }

    /**
     * Checks for the user and password in the server request params
     *
     * @param array<string, mixed> $serverParams Server params from \Psr\Http\Message\ServerRequestInterface::getServerParams()
     * @return bool
     */
    protected function checkServerParams(array $serverParams): bool
    {
        return !isset($serverParams['PHP_AUTH_USER'], $serverParams['PHP_AUTH_PW'])
            || !is_string($serverParams['PHP_AUTH_USER'])
            || $serverParams['PHP_AUTH_USER'] === ''
            || !is_string($serverParams['PHP_AUTH_PW'])
            || $serverParams['PHP_AUTH_PW'] === '';
    }

    /**
     * Get a user based on information in the request. Used by cookie-less auth for stateless clients.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request object.
     * @return \ArrayAccess|null User entity or null on failure.
     */
    public function getUser(ServerRequestInterface $request): ?ArrayAccess
    {
        $serverParams = $request->getServerParams();
        if ($this->checkServerParams($serverParams)) {
            return null;
        }

        return $this->identifier->identify([
            IdentifierInterface::CREDENTIAL_USERNAME => $serverParams['PHP_AUTH_USER'],
            IdentifierInterface::CREDENTIAL_PASSWORD => $serverParams['PHP_AUTH_PW'],
        ]);
    }

    /**
     * Create a challenge exception for basic auth challenge.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request A request object.
     * @return void
     * @throws \Phauthentic\Authentication\Authenticator\Exception\UnauthorizedException
     */
    public function unauthorizedChallenge(ServerRequestInterface $request): void
    {
        throw new UnauthorizedException($this->loginHeaders($request), '');
    }

    /**
     * Generate the login headers
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request object.
     * @return array<string, string> Headers for logging in.
     */
    protected function loginHeaders(ServerRequestInterface $request): array
    {
        $server = $request->getServerParams();
        $realm = $this->realm ?: $server['SERVER_NAME'];

        return ['WWW-Authenticate' => sprintf('Basic realm="%s"', $realm)];
    }
}

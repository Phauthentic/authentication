<?php

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

declare(strict_types=1);

namespace Phauthentic\Authentication;

use Phauthentic\Authentication\Authenticator\AuthenticatorInterface;
use Phauthentic\Authentication\Authenticator\ResultInterface;
use Phauthentic\Authentication\Identity\IdentityInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface AuthenticationServiceInterface
{
    /**
     * Authenticate the request against the configured authentication adapters.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @return bool
     */
    public function authenticate(ServerRequestInterface $request): bool;

    /**
     * Gets an identity object or null if identity has not been resolved.
     *
     * @return null|\Phauthentic\Authentication\Identity\IdentityInterface
     */
    public function getIdentity(): ?IdentityInterface;

    /**
     * Gets the successful authenticator instance if one was successful after calling authenticate
     *
     * @return \Phauthentic\Authentication\Authenticator\AuthenticatorInterface|null
     */
    public function getSuccessfulAuthenticator(): ?AuthenticatorInterface;

    /**
     * Gets the result of the last authenticate() call.
     *
     * @return \Phauthentic\Authentication\Authenticator\ResultInterface|null Authentication result interface
     */
    public function getResult(): ?ResultInterface;

    /**
     * Returns a list of failed authenticators and their results after an authenticate() call
     *
     * @return \Phauthentic\Authentication\Authenticator\FailureInterface[]
     */
    public function getFailures(): array;

    /**
     * Clears the identity from authenticators that store them and the request
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @return \Phauthentic\Authentication\PersistenceResultInterface
     */
    public function clearIdentity(ServerRequestInterface $request, ResponseInterface $response): PersistenceResultInterface;

    /**
     * Sets identity data and persists it in the authenticators that support it.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param \Phauthentic\Authentication\Identity\IdentityInterface|null $identity Identity object.
     * @return \Phauthentic\Authentication\PersistenceResultInterface
     */
    public function persistIdentity(ServerRequestInterface $request, ResponseInterface $response, IdentityInterface $identity = null): PersistenceResultInterface;
}

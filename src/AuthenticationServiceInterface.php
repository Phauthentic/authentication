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
namespace Authentication;

use Authentication\Authenticator\AuthenticatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface AuthenticationServiceInterface
{
    /**
     * Authenticate the request against the configured authentication adapters.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @return bool True on success
     */
    public function authenticate(ServerRequestInterface $request): bool;

    /**
     * Gets an identity object or null if identity has not been resolved.
     *
     * @return null|\Authentication\IdentityInterface
     */
    public function getIdentity(): ?IdentityInterface;

    /**
     * Gets the successful authenticator instance if one was successful after calling authenticate
     *
     * @return \Authentication\Authenticator\AuthenticatorInterface|null
     */
    public function getAuthenticationProvider(): ?AuthenticatorInterface;

    /**
     * Gets the result of the last authenticate() call.
     *
     * @return \Authentication\Authenticator\ResultInterface|null Authentication result interface
     */
    public function getResult();

    /**
     * Returns a list of failed authenticators and their results after an authenticate() call
     *
     * @return \Authentication\Authenticator\FailureInterface[]
     */
    public function getFailures(): array;

    /**
     * Clears the identity from authenticators that store them and the request
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @return \Authentication\PersistenceResultInterface
     */
    public function clearIdentity(ServerRequestInterface $request, ResponseInterface $response): PersistenceResultInterface;

    /**
     * Sets identity data and persists it in the authenticators that support it.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param \Authentication\IdentityInterface $identity Identity data.
     * @return \Authentication\PersistenceResultInterface
     */
    public function persistIdentity(ServerRequestInterface $request, ResponseInterface $response, ?IdentityInterface $identity): PersistenceResultInterface;
}

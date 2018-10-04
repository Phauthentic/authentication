<?php
declare(strict_types=1);
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
namespace Phauthentic\Authentication;

use ArrayAccess;
use Phauthentic\Authentication\Authenticator\AuthenticatorCollectionInterface;
use Phauthentic\Authentication\Authenticator\AuthenticatorInterface;
use Phauthentic\Authentication\Authenticator\Failure;
use Phauthentic\Authentication\Authenticator\PersistenceInterface;
use Phauthentic\Authentication\Authenticator\ResultInterface;
use Phauthentic\Authentication\Authenticator\StatelessInterface;
use Phauthentic\Authentication\Identity\IdentityFactoryInterface;
use Phauthentic\Authentication\Identity\IdentityInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * Authentication Service
 */
class AuthenticationService implements AuthenticationServiceInterface
{
    /**
     * Authenticator collection
     *
     * @var \Phauthentic\Authentication\Authenticator\AuthenticatorCollection
     */
    protected $authenticators;

    /**
     * Authenticator that successfully authenticated the identity.
     *
     * @var \Phauthentic\Authentication\Authenticator\AuthenticatorInterface|null
     */
    protected $successfulAuthenticator;

    /**
     * A list of failed authenticators after an authentication attempt
     *
     * @var \Phauthentic\Authentication\Authenticator\FailureInterface[]
     */
    protected $failures = [];

    /**
     * Identity object.
     *
     * @var \Phauthentic\Authentication\Identity\IdentityInterface|null
     */
    protected $identity;

    /**
     * Result of the last authenticate() call.
     *
     * @var \Phauthentic\Authentication\Authenticator\ResultInterface|null
     */
    protected $result;

    /**
     * Identity factory used to instantiate an identity object
     *
     * @var \Phauthentic\Authentication\Identity\IdentityFactoryInterface
     */
    protected $identityFactory;

    /**
     * Constructor
     *
     * @param \Phauthentic\Authentication\Authenticator\AuthenticatorCollection $authenticators Authenticator collection.
     * @param \Phauthentic\Authentication\Identity\IdentityFactoryInterface $factory Identity factory.
     */
    public function __construct(
        AuthenticatorCollectionInterface $authenticators,
        IdentityFactoryInterface $factory
    ) {
        $this->authenticators = $authenticators;
        $this->identityFactory = $factory;
    }

    /**
     * Access the authenticator collection
     *
     * @return \Phauthentic\Authentication\Authenticator\AuthenticatorCollection
     */
    public function authenticators(): AuthenticatorCollectionInterface
    {
        return $this->authenticators;
    }

    /**
     * Checks if at least one authenticator is in the collection
     *
     * @throws \RuntimeException
     * @return void
     */
    protected function checkAuthenticators(): void
    {
        if ($this->authenticators()->isEmpty()) {
            throw new RuntimeException(
                'No authenticators loaded. You need to load at least one authenticator.'
            );
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException Throws a runtime exception when no authenticators are loaded.
     */
    public function authenticate(ServerRequestInterface $request): bool
    {
        $this->checkAuthenticators();
        $this->identity = null;
        $this->successfulAuthenticator = null;
        $this->failures = [];

        $result = null;
        foreach ($this->authenticators() as $authenticator) {
            /* @var $authenticator \Phauthentic\Authentication\Authenticator\AuthenticatorInterface */
            $result = $authenticator->authenticate($request);
            if ($result->isValid()) {
                $this->successfulAuthenticator = $authenticator;
                $this->result = $result;

                return true;
            }

            if (!$result->isValid()) {
                if ($authenticator instanceof StatelessInterface) {
                    $authenticator->unauthorizedChallenge($request);
                }

                $this->failures[] = new Failure($authenticator, $result);
            }
        }

        $this->result = $result;

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getFailures(): array
    {
        return $this->failures;
    }

    /**
     * Clears the identity from authenticators that store them and the request
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @return \Phauthentic\Authentication\PersistenceResultInterface Return an array containing the request and response objects.
     */
    public function clearIdentity(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): PersistenceResultInterface {
        foreach ($this->authenticators() as $authenticator) {
            if ($authenticator instanceof PersistenceInterface) {
                $response = $authenticator->clearIdentity($request, $response);
            }
        }

        return new PersistenceResult($request, $response);
    }

    /**
     * Sets identity data and persists it in the authenticators that support it.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param \Phauthentic\Authentication\Identity\IdentityInterface|null $identity Identity.
     * @return \Phauthentic\Authentication\PersistenceResultInterface
     */
    public function persistIdentity(
        ServerRequestInterface $request,
        ResponseInterface $response,
        IdentityInterface $identity = null
    ): PersistenceResultInterface {
        if ($identity === null) {
            $identity = $this->getIdentity();
        }

        if ($identity !== null) {
            foreach ($this->authenticators() as $authenticator) {
                if ($authenticator instanceof PersistenceInterface) {
                    $response = $authenticator->persistIdentity($request, $response, $identity->getOriginalData());
                }
            }
        }

        return new PersistenceResult($request, $response);
    }

    /**
     * Gets the successful authenticator instance if one was successful after calling authenticate
     *
     * @return \Phauthentic\Authentication\Authenticator\AuthenticatorInterface|null
     */
    public function getSuccessfulAuthenticator(): ?AuthenticatorInterface
    {
        return $this->successfulAuthenticator;
    }

    /**
     * Gets the result of the last authenticate() call.
     *
     * @return \Phauthentic\Authentication\Authenticator\ResultInterface|null Authentication result interface
     */
    public function getResult(): ?ResultInterface
    {
        return $this->result;
    }

    /**
     * Gets an identity object
     *
     * @return null|\Phauthentic\Authentication\Identity\IdentityInterface
     */
    public function getIdentity(): ?IdentityInterface
    {
        if ($this->result === null || !$this->result->isValid()) {
            return null;
        }

        $data = $this->result->getData();
        if ($data instanceof IdentityInterface || $data === null) {
            return $data;
        }

        if ($this->identity === null) {
            $this->identity = $this->buildIdentity($data);
        }

        return $this->identity;
    }

    /**
     * Builds the identity object
     *
     * @param \ArrayAccess $data Identity data
     * @return \Phauthentic\Authentication\Identity\IdentityInterface
     */
    public function buildIdentity(ArrayAccess $data): IdentityInterface
    {
        return $this->identityFactory->create($data);
    }
}

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

use Authentication\Authenticator\AuthenticatorCollectionInterface;
use Authentication\Authenticator\AuthenticatorInterface;
use Authentication\Authenticator\Failure;
use Authentication\Authenticator\FailureInterface;
use Authentication\Authenticator\PersistenceInterface;
use Authentication\Authenticator\ResultInterface;
use Authentication\Authenticator\StatelessInterface;
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
     * @var \Authentication\Authenticator\AuthenticatorCollection
     */
    protected $authenticators;

    /**
     * Authenticator that successfully authenticated the identity.
     *
     * @var \Authentication\Authenticator\AuthenticatorInterface|null
     */
    protected $successfulAuthenticator;

    /**
     * A list of failed authenticators after an authentication attempt
     *
     * @var \Authentication\Authenticator\FailureInterface[]
     */
    protected $failures = [];

    /**
     * Result of the last authenticate() call.
     *
     * @var \Authentication\Authenticator\ResultInterface|null
     */
    protected $result;

    /**
     * Identity class used to instantiate an identity object
     *
     * @var string
     */
    protected $identityClass = Identity::class;

    /**
     * Request attribute for the identity
     *
     * @var string
     */
    protected $identityAttribute = 'identity';

    /**
     * Constructor
     *
     * @param array $config Configuration options.
     */
    public function __construct(AuthenticatorCollectionInterface $authenticators) {
        $this->authenticators = $authenticators;
    }

    /**
     * Sets the identity class
     *
     * @param string $class Class name
     * @return $this
     */
    public function setIdentityClass($class): self
    {
        $this->identityClass = $class;

        return $this;
    }

    /**
     * Sets the identity attribute
     *
     * @param string $attribute Attribute name
     * @return $this
     */
    public function setIdentityAttribute($attribute): self
    {
        $this->identityAttribute = $attribute;

        return $this;
    }

    /**
     * Access the authenticator collection
     *
     * @return \Authentication\Authenticator\AuthenticatorCollection
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
     * @return bool
     */
    public function authenticate(ServerRequestInterface $request): bool
    {
        $this->checkAuthenticators();
        $this->failures = [];

        foreach ($this->authenticators() as $authenticator) {
            /* @var $authenticator \Authentication\Authenticator\AuthenticatorInterface */
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

        $this->successfulAuthenticator = null;
        $this->result = $result;

        return false;
    }

    /**
     * Returns a list of failed authenticators after an authenticate() call
     *
     * @return array
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
     * @return array Return an array containing the request and response objects.
     */
    public function clearIdentity(ServerRequestInterface $request, ResponseInterface $response): PersistenceResultInterface
    {
        foreach ($this->authenticators() as $authenticator) {
            if ($authenticator instanceof PersistenceInterface) {
                $result = $authenticator->clearIdentity($request, $response);
                $request = $result['request'];
                $response = $result['response'];
            }
        }

        return new PersistenceResult($request, $response);
    }

    /**
     * Sets identity data and persists it in the authenticators that support it.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param \Authentication\IdentityInterface $identity Identity data.
     * @return array
     */
    public function persistIdentity(ServerRequestInterface $request, ResponseInterface $response, ?IdentityInterface $identity): PersistenceResultInterface
    {
        if (is_null($identity)) {
            $identity = $this->getIdentity();
        }

        foreach ($this->authenticators() as $authenticator) {
            if ($authenticator instanceof PersistenceInterface) {
                $result = $authenticator->persistIdentity($request, $response, $identity);
                $request = $result['request'];
                $response = $result['response'];
            }
        }

        return new PersistenceResult($request, $response);
    }

    /**
     * Gets the successful authenticator instance if one was successful after calling authenticate
     *
     * @return \Authentication\Authenticator\AuthenticatorInterface|null
     */
    public function getAuthenticationProvider(): ?AuthenticatorInterface
    {
        return $this->successfulAuthenticator;
    }

    /**
     * Gets the result of the last authenticate() call.
     *
     * @return \Authentication\Authenticator\ResultInterface|null Authentication result interface
     */
    public function getResult(): ?ResultInterface
    {
        return $this->result;
    }

    /**
     * Gets an identity object
     *
     * @return null|\Authentication\IdentityInterface
     */
    public function getIdentity(): ?IdentityInterface
    {
        if ($this->result === null || !$this->result->isValid()) {
            return null;
        }

        $identity = $this->result->getData();
        if (!($identity instanceof IdentityInterface)) {
            $identity = $this->buildIdentity($identity);
        }

        return $identity;
    }

    /**
     * Builds the identity object
     *
     * @param \ArrayAccess|array $identityData Identity data
     * @return \Authentication\IdentityInterface
     */
    public function buildIdentity($identityData): IdentityInterface
    {
        $class = $this->identityClass;

        if (is_callable($class)) {
            $identity = $class($identityData);
        } else {
            $identity = new $class($identityData);
        }

        return $identity;
    }
}

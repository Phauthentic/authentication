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
use Authentication\Authenticator\Result;
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
    public function authenticators()
    {
        return $this->authenticators;
    }

    /**
     * Checks if at least one authenticator is in the collection
     *
     * @throws \RuntimeException
     * @return void
     */
    protected function checkAuthenticators()
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

        foreach ($this->authenticators() as $authenticator) {
            $result = $authenticator->authenticate($request);
            if ($result->isValid()) {
                $this->successfulAuthenticator = $authenticator;
                $this->result = $result;

                return true;
            }

            if (!$result->isValid() && $authenticator instanceof StatelessInterface) {
                $authenticator->unauthorizedChallenge($request);
            }
        }

        $this->successfulAuthenticator = null;
        $this->result = $result;

        return false;
    }

    /**
     * Clears the identity from authenticators that store them and the request
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @return array Return an array containing the request and response objects.
     */
    public function clearIdentity(ServerRequestInterface $request, ResponseInterface $response)
    {
        foreach ($this->authenticators() as $authenticator) {
            if ($authenticator instanceof PersistenceInterface) {
                $result = $authenticator->persistence()->clearIdentity($request, $response);
                $request = $result['request'];
                $response = $result['response'];
            }
        }

        return [
            'request' => $request->withoutAttribute($this->identityAttribute),
            'response' => $response
        ];
    }

    /**
     * Sets identity data and persists it in the authenticators that support it.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param \Authentication\IdentityInterface $identity Identity data.
     * @return array
     */
    public function persistIdentity(ServerRequestInterface $request, ResponseInterface $response, ?IdentityInterface $identity)
    {
        if (is_null($identity)) {
            $identity = $this->getIdentity();
        }

        foreach ($this->authenticators() as $authenticator) {
            if ($authenticator instanceof PersistenceInterface) {
                $result = $authenticator->persistence()->persistIdentity($request, $response, $identity);
                $request = $result['request'];
                $response = $result['response'];
            }
        }

        return [
            'request' => $request->withAttribute($this->identityAttribute, $identity),
            'response' => $response
        ];
    }

    /**
     * Gets the successful authenticator instance if one was successful after calling authenticate
     *
     * @return \Authentication\Authenticator\AuthenticatorInterface|null
     */
    public function getAuthenticationProvider()
    {
        return $this->successfulAuthenticator;
    }

    /**
     * Gets the result of the last authenticate() call.
     *
     * @return \Authentication\Authenticator\ResultInterface|null Authentication result interface
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Gets an identity object
     *
     * @return null|\Authentication\IdentityInterface
     */
    public function getIdentity()
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

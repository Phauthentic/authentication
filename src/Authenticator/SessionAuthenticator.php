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
namespace Authentication\Authenticator;

use ArrayAccess;
use ArrayObject;
use Authentication\Authenticator\Persistence\PersistenceInterface as Persistence;
use Authentication\Authenticator\Persistence\SessionPersistenceInterface;
use Authentication\Identifier\IdentifierInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Session Authenticator
 */
class SessionAuthenticator extends AbstractAuthenticator implements PersistenceInterface
{

    /**
     * Default config for this object.
     * - `fields` The fields to use to verify a user by.
     * - `sessionKey` Session key.
     * - `identify` Whether or not to identify user data stored in a session.
     *
     * @var array
     */
    protected $defaultConfig = [
        'fields' => [
            IdentifierInterface::CREDENTIAL_USERNAME => 'username'
        ],
        'sessionKey' => 'Auth',
        'identify' => false,
        'identityAttribute' => 'identity',
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct(
        IdentifierCollection $identifiers,
        SessionPersistenceInterface $persistence,
        array $config = []
    ) {
        parent::__construct($identifiers, $config);

        $this->persistence = $persistence;
    }

    /**
     * @inheritDoc
     */
    public function persistence(): Persistence
    {
        return $this->persistence;
    }

    /**
     * Authenticate a user using session data.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request to authenticate with.
     * @param \Psr\Http\Message\ResponseInterface $response The response to add headers to.
     * @return \Authentication\Authenticator\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request)
    {
        $sessionKey = $this->config['sessionKey'];
        $session = $request->getAttribute('session');
        $user = $session->read($sessionKey);

        if (empty($user)) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
        }

        if ($this->config['identify'] === true) {
            $credentials = [];
            foreach ($this->config['fields'] as $key => $field) {
                $credentials[$key] = $user[$field];
            }
            $user = $this->_identifier->identify($credentials);

            if (empty($user)) {
                return new Result(null, Result::FAILURE_CREDENTIALS_INVALID);
            }
        }

        if (!($user instanceof ArrayAccess)) {
            $user = new ArrayObject($user);
        }

        return new Result($user, Result::SUCCESS);
    }

    /**
     * {@inheritDoc}
     */
    public function persistIdentity($identity)
    {
        $this->persistence->persistIdentity($identity);
        /*
        $sessionKey = $this->config['sessionKey'];
        $request->getAttribute('session')->write($sessionKey, $identity);

        return [
            'request' => $request,
            'response' => $response,
        ];
        */
    }

    /**
     * {@inheritDoc}
     */
    public function clearIdentity()
    {
        $this->persistence->clearIdentity();
        /*
        $sessionKey = $this->config['sessionKey'];
        $request->getAttribute('session')->delete($sessionKey);

        return [
            'request' => $request->withoutAttribute($this->config['identityAttribute']),
            'response' => $response
        ];
        */
    }
}

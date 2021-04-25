<?php
declare(strict_types=1);
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
namespace Phauthentic\Authentication\Authenticator;

use ArrayAccess;
use ArrayObject;
use Phauthentic\Authentication\Authenticator\Storage\StorageInterface;
use Phauthentic\Authentication\Identifier\IdentifierInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Session Authenticator
 */
class SessionAuthenticator extends AbstractAuthenticator implements PersistenceInterface
{

    /**
     * @var array<string, string>
     */
    protected array $credentialFields = [
        IdentifierInterface::CREDENTIAL_USERNAME => 'username',
    ];

    /**
     * @var bool
     */
    protected bool $verify = false;

    /**
     * @var \Phauthentic\Authentication\Authenticator\Storage\StorageInterface
     */
    protected StorageInterface $storage;

    /**
     * {@inheritDoc}
     */
    public function __construct(
        IdentifierInterface $identifiers,
        StorageInterface $storage
    ) {
        parent::__construct($identifiers);

        $this->storage = $storage;
    }

    /**
     * Set the fields to use to verify a user by.
     *
     * @param array<string, string> $fields Credential fields.
     * @return $this
     */
    public function setCredentialFields(array $fields): self
    {
        $this->credentialFields = $fields;

        return $this;
    }

    /**
     * Enable identity verification after it is retrieved from the session storage.
     *
     * @return $this
     */
    public function enableVerification(): self
    {
        $this->verify = true;

        return $this;
    }

    /**
     * Disable identity verification after it is retrieved from the session storage.
     *
     * @return $this
     */
    public function disableVerification(): self
    {
        $this->verify = false;

        return $this;
    }

    /**
     * Authenticate a user using session data.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request to authenticate with.
     * @return \Phauthentic\Authentication\Authenticator\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request): ResultInterface
    {
        $user = $this->storage->read($request);

        if (empty($user)) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
        }

        if ($this->verify) {
            $credentials = [];
            foreach ($this->credentialFields as $key => $field) {
                $credentials[$key] = $user[$field];
            }
            $user = $this->identifier->identify($credentials);

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
    public function clearIdentity(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->storage->clear($request, $response);
    }

    /**
     * {@inheritDoc}
     */
    public function persistIdentity(ServerRequestInterface $request, ResponseInterface $response, ArrayAccess $data): ResponseInterface
    {
        return $this->storage->write($request, $response, $data);
    }
}

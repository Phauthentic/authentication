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
namespace Authentication\Authenticator;

use ArrayAccess;
use Authentication\Authenticator\Storage\StorageInterface;
use Authentication\Identifier\IdentifierInterface;
use Authentication\PasswordHasher\PasswordHasherInterface;
use Authentication\UrlChecker\UrlCheckerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Cookie Authenticator
 *
 * Authenticates an identity based on a cookies data.
 */
class CookieAuthenticator extends AbstractAuthenticator implements PersistenceInterface
{

    use CredentialFieldsTrait;
    use UrlAwareTrait;

    /**
     * Password hasher
     *
     * @var \Authentication\PasswordHasher\PasswordHasherInterface
     */
    protected $passwordHasher;

    /**
     * Storage Implementation
     *
     * @var \Authentication\Authenticator\Storage\StorageInterface
     */
    protected $storage;

    /**
     * "Remember me" field
     *
     * @var string
     */
    protected $rememberMeField = 'remember_me';

    /**
     * {@inheritDoc}
     */
    public function __construct(
        IdentifierInterface $identifier,
        StorageInterface $storage,
        PasswordHasherInterface $passwordHasher,
        UrlCheckerInterface $urlChecker
    ) {
        parent::__construct($identifier);

        $this->storage = $storage;
        $this->passwordHasher = $passwordHasher;
        $this->urlChecker = $urlChecker;
    }

    /**
     * Sets "remember me" form field name.
     *
     * @param string $field Field name.
     * @return $this
     */
    public function setRememberMeField(string $field): self
    {
        $this->rememberMeField = $field;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(ServerRequestInterface $request): ResultInterface
    {
        $token = $this->storage->read($request);

        if ($token === null) {
            return new Result(null, Result::FAILURE_CREDENTIALS_MISSING, [
                'Login credentials not found'
            ]);
        }

        if (!is_array($token) || count($token) !== 2) {
            return new Result(null, Result::FAILURE_CREDENTIALS_INVALID, [
                'Cookie token is invalid.'
            ]);
        }

        list($username, $tokenHash) = $token;

        $data = $this->identifier->identify([
            IdentifierInterface::CREDENTIAL_USERNAME => $username,
        ]);

        if (empty($data)) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND, $this->identifier->getErrors());
        }

        if (!$this->checkToken($data, $tokenHash)) {
            return new Result(null, Result::FAILURE_CREDENTIALS_INVALID, [
                'Cookie token does not match'
            ]);
        }

        return new Result($data, Result::SUCCESS);
    }

    /**
     * {@inheritDoc}
     */
    public function persistIdentity(ServerRequestInterface $request, ResponseInterface $response, ArrayAccess $data): ResponseInterface
    {
        $field = $this->rememberMeField;
        $bodyData = $request->getParsedBody();

        if (!$this->checkUrl($request) || !is_array($bodyData) || empty($bodyData[$field])) {
            return $response;
        }

        $token = $this->createToken($data);

        return $this->storage->write($request, $response, $token);
    }

    /**
     * {@inheritDoc}
     */
    public function clearIdentity(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->storage->clear($request, $response);
    }

    /**
     * Creates a plain part of a cookie token.
     *
     * Returns concatenated username and password hash.
     *
     * @param \ArrayAccess $data Identity data.
     * @return string
     */
    protected function createPlainToken(ArrayAccess $data): string
    {
        $usernameField = $this->credentialFields[IdentifierInterface::CREDENTIAL_USERNAME];
        $passwordField = $this->credentialFields[IdentifierInterface::CREDENTIAL_PASSWORD];

        return $data[$usernameField] . $data[$passwordField];
    }

    /**
     * Creates a full cookie token serialized as a JSON sting.
     *
     * Cookie token consists of a username and hashed username + password hash.
     *
     * @param \ArrayAccess $data Identity data.
     * @return string
     */
    protected function createToken(ArrayAccess $data): string
    {
        $plain = $this->createPlainToken($data);
        $hash = $this->passwordHasher->hash($plain);

        $usernameField = $this->credentialFields[IdentifierInterface::CREDENTIAL_USERNAME];

        return (string)json_encode([$data[$usernameField], $hash]);
    }

    /**
     * Checks whether a token hash matches the identity data.
     *
     * @param \ArrayAccess $data Identity data.
     * @param string $tokenHash Hashed part of a cookie token.
     * @return bool
     */
    protected function checkToken(ArrayAccess $data, $tokenHash): bool
    {
        $plain = $this->createPlainToken($data);

        return $this->passwordHasher->check($plain, $tokenHash);
    }
}

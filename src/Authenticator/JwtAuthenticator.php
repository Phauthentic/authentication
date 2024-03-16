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

namespace Phauthentic\Authentication\Authenticator;

use ArrayObject;
use Phauthentic\Authentication\Identifier\IdentifierInterface;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Jwt Authenticator
 */
class JwtAuthenticator extends TokenAuthenticator
{
    /**
     * Query param
     *
     * @var null|string
     */
    protected ?string $queryParam = 'token';

    /**
     * Header
     *
     * @var null|string
     */
    protected ?string $header = 'Authorization';

    /**
     * Token Prefix
     *
     * @var null|string
     */
    protected ?string $tokenPrefix = 'bearer';

    /**
     * Hashing algorithms
     *
     * @var array<int, string>
     */
    protected array $algorithms = [
        'HS256'
    ];

    /**
     * Return payload
     *
     * @var bool
     */
    protected bool $returnPayload = true;

    /**
     * Secret key
     *
     * @var null|string
     */
    protected ?string $secretKey;

    /**
     * Payload data.
     *
     * @var object|null
     */
    protected ?object $payload = null;

    /**
     * {@inheritDoc}
     */
    public function __construct(IdentifierInterface $identifier, string $secretKey)
    {
        parent::__construct($identifier);

        $this->secretKey = $secretKey;
    }

    /**
     * Sets algorithms to use
     *
     * @param array<int, string> $algorithms List of algorithms
     * @return $this
     */
    public function setAlgorithms(array $algorithms): self
    {
        $this->algorithms = $algorithms;

        return $this;
    }

    /**
     * Sets return payload.
     *
     * @param bool $return Return payload.
     * @return $this
     */
    public function setReturnPayload(bool $return): self
    {
        $this->returnPayload = $return;

        return $this;
    }

    /**
     * Sets secret key.
     *
     * @param string $key Secret key.
     * @return $this
     */
    public function setSecretKey(string $key): self
    {
        $this->secretKey = $key;

        return $this;
    }

    /**
     * Authenticates the identity based on a JWT token contained in a request.
     *
     * @link https://jwt.io/
     * @param \Psr\Http\Message\ServerRequestInterface $request The request that contains login information.
     * @return \Phauthentic\Authentication\Authenticator\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request): ResultInterface
    {
        try {
            $result = $this->getPayload($request);
        } catch (Exception $e) {
            return new Result(
                null,
                Result::FAILURE_CREDENTIALS_INVALID,
                [
                    'message' => $e->getMessage(),
                    'exception' => $e
                ]
            );
        }

        if (!($result instanceof stdClass)) {
            return new Result(null, Result::FAILURE_CREDENTIALS_INVALID);
        }

        $result = json_decode((string)json_encode($result), true);

        $key = IdentifierInterface::CREDENTIAL_JWT_SUBJECT;
        if (empty($result[$key])) {
            return new Result(null, Result::FAILURE_CREDENTIALS_MISSING);
        }

        if ($this->returnPayload) {
            $user = new ArrayObject($result);

            return new Result($user, Result::SUCCESS);
        }

        $user = $this->identifier->identify([
            $key => $result[$key]
        ]);

        if (empty($user)) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND, $this->identifier->getErrors());
        }

        return new Result($user, Result::SUCCESS);
    }

    /**
     * Get payload data.
     *
     * @param \Psr\Http\Message\ServerRequestInterface|null $request Request to get authentication information from.
     * @return object|null Payload object on success, null on failure
     */
    public function getPayload(ServerRequestInterface $request = null)
    {
        if (!$request) {
            return $this->payload;
        }

        $payload = null;
        $token = $this->getToken($request);

        if ($token !== null) {
            $payload = $this->decodeToken($token);
        }

        $this->payload = $payload;

        return $this->payload;
    }

    /**
     * Decode JWT token.
     *
     * @param string $token JWT token to decode.
     * @return object|null The JWT's payload as a PHP object, null on failure.
     */
    protected function decodeToken($token)
    {
        return JWT::decode(
            $token,
            new Key((string)$this->secretKey, $this->algorithms[0])
        );
    }
}

<?php

/**
 * Copyright (c) Phauthentic (https://github.com/Phauthentic)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Phauthentic (https://github.com/Phauthentic)
 * @link          https://github.com/Phauthentic
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Phauthentic\Authentication\Authenticator\Storage;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * "Core php" or "native php" session adapter
 */
class NativePhpSessionStorage implements StorageInterface
{
    /**
     * @var string
     */
    protected $key = 'Auth';

    /**
     * Constructor
     *
     * @param string $sessionKey Session key.
     */
    public function __construct(string $sessionKey)
    {
        $this->key = $sessionKey;
    }

    /**
     * Set session key for stored identity.
     *
     * @param string $key Session key.
     * @return $this
     */
    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clear(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        unset($_SESSION[$this->key]);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function read(ServerRequestInterface $request)
    {
        if (!isset($_SESSION[$this->key])) {
            return null;
        }

        return $_SESSION[$this->key];
    }

    /**
     * {@inheritDoc}
     */
    public function write(ServerRequestInterface $request, ResponseInterface $response, $data): ResponseInterface
    {
        $_SESSION[$this->key] = (array)$data;

        return $response;
    }
}

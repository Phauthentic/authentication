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
 * Storage Interface
 */
interface StorageInterface
{
    /**
     * Reads the data from the storage.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request object.
     * @return null|mixed
     */
    public function read(ServerRequestInterface $request);

    /**
     * Persists data in the storage.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request object.
     * @param \Psr\Http\Message\ResponseInterface $response The response object.
     * @param mixed $data Data to persist.
     * @return ResponseInterface Returns the modified response object
     */
    public function write(ServerRequestInterface $request, ResponseInterface $response, $data): ResponseInterface;

    /**
     * Clears the data form a storage.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request object.
     * @param \Psr\Http\Message\ResponseInterface $response The response object.
     * @return ResponseInterface Returns the modified response object
     */
    public function clear(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}

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
namespace Authentication\Authenticator\Storage;

use Cake\Http\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Storage adapter for the CakePHP Session
 */
class CakeSessionStorage implements StorageInterface
{

    /**
     * @var string
     */
    protected $key = 'Auth';

    /**
     * @var string
     */
    protected $attribute = 'session';

    /**
     * Set request attribute name for a session object.
     *
     * @param string $attribute Request attribute name.
     * @return $this
     */
    public function setAttribute(string $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
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
        $this->getSession($request)->delete($this->key);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function read(ServerRequestInterface $request)
    {
        return $this->getSession($request)->read($this->key);
    }

    /**
     * {@inheritDoc}
     */
    public function write(ServerRequestInterface $request, ResponseInterface $response, $data): ResponseInterface
    {
        $this->getSession($request)->write($this->key, $data);

        return $response;
    }

    /**
     * Returns session object.
     *
     * @param ServerRequestInterface $request Request.
     * @return Session
     */
    protected function getSession(ServerRequestInterface $request): Session
    {
        return $request->getAttribute($this->attribute);
    }
}

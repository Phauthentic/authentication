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

use Authentication\Authenticator\Storage\StorageInterface;
use Cake\Http\Cookie\Cookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Storage adapter for the CakePHP Cookie
 */
class CakeCookieStorage implements StorageInterface
{
    /**
     * @var array
     */
    protected $cookieData = [
        'name' => 'CookieAuth',
        'expire' => null,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httpOnly' => false
    ];

    /**
     * Sets cookie data.
     *
     * @param array $data Cookie data.
     * @param bool $merge Whether to merge or replace.
     * @return $this
     */
    public function setCookieData(array $data, bool $merge = true): self
    {
        if ($merge) {
            $this->cookieData = $data + $this->cookieData;
        } else {
            $this->cookieData = $data;
        }

        return $this;
    }

    /**
     * Creates a cookie instance with configured defaults.
     *
     * @param mixed $value Cookie value.
     * @return \Cake\Http\Cookie\CookieInterface
     */
    protected function _createCookie($value)
    {
        $data = $this->cookieData;

        $cookie = new Cookie(
            $data['name'],
            $value,
            $data['expire'],
            $data['path'],
            $data['domain'],
            $data['secure'],
            $data['httpOnly']
        );

        return $cookie;
    }

    /**
     * {@inheritDoc}
     */
    public function read(ServerRequestInterface $request)
    {
        $cookies = $request->getCookieParams();
        $cookieName = $this->cookieData['name'];

        if (!isset($cookies[$cookieName])) {
            return null;
        }

        $value = $cookies[$cookieName];

        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function clear(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $cookie = $this->_createCookie(null)->withExpired();

        return $response->withAddedHeader('Set-Cookie', $cookie->toHeaderValue());
    }

    /**
     * {@inheritDoc}
     */
    public function write(ServerRequestInterface $request, ResponseInterface $response, $data): ResponseInterface
    {
        if (!is_string($data)) {
            $data = json_encode($data);
        }

        $cookie = $this->_createCookie($data);

        return $response->withAddedHeader('Set-Cookie', $cookie->toHeaderValue());
    }
}

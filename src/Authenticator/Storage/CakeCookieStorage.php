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
namespace Cake\Auth\Authenticator\Storage;

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
     * Default Config
     *
     * @var array
     */
    protected $defaultConfig = [
        'cookie' => [
            'name' => 'CookieAuth',
            'expire' => null,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httpOnly' => false
        ]
    ];

    /**
     * Config
     *
     * @var array
     */
    protected $config = [];

    public function __construct(array $config = [])
    {
        $this->config = array_merge_recursive($this->defaultConfig, $config);
    }

    /**
     * Creates a cookie instance with configured defaults.
     *
     * @param mixed $value Cookie value.
     * @return \Cake\Http\Cookie\CookieInterface
     */
    protected function _createCookie($value)
    {
        $data = $this->config['cookie'];

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

    public function read(ServerRequestInterface $request)
    {
        $cookies = $request->getCookieParams();
        $cookieName = $this->config['cookie']['name'];

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

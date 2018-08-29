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
namespace Cake\Auth\Authenticator\Persistence;

use Authentication\Authenticator\Persistence\CookiePersistenceInterface;
use Authentication\Authenticator\Persistence\PersistenceInterface;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Cookie\CookieCollection;
use Cake\Http\ServerRequest;

/**
 * Persistence adapter for the CakePHP Cookie
 */
class CakeCookie implements CookiePersistenceInterface
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

    public function __construct(ServerRequest $serverRequest, array $config = [])
    {
        $this->config = array_merge_recursive($this->defaultConfig, $config);
        $this->request = $serverRequest;
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

    public function getIdentityData() {
        $cookies = $this->request->getCookieParams();
        $cookieName = $this->config['cookie']['name'];

        if (!isset($cookies[$cookieName])) {
            return new Result(null, Result::FAILURE_CREDENTIALS_MISSING, [
                'Login credentials not found'
            ]);
        }

        if (is_array($cookies[$cookieName])) {
            $token = $cookies[$cookieName];
        } else {
            $token = json_decode($cookies[$cookieName], true);
        }

        if ($token === null || count($token) !== 2) {
            return new Result(null, Result::FAILURE_CREDENTIALS_INVALID, [
                'Cookie token is invalid.'
            ]);
        }

        list($username, $tokenHash) = $token;
    }

    /**
     * Persists the users data
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request object.
     * @param \Psr\Http\Message\ResponseInterface $response The response object.
     * @param \ArrayAccess|array $identity Identity data to persist.
     * @return array Returns an array containing the request and response object
     */
    public function persistIdentity($identity) {
        // TODO: Implement persistIdentity() method.
    }

    /**
     * Clears the identity data
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request object.
     * @param \Psr\Http\Message\ResponseInterface $response The response object.
     * @return array Returns an array containing the request and response object
     */
    public function clearIdentity() {
        $cookie = $this->_createCookie(null)->withExpired();

        $this->response = $this->response->withAddedHeader('Set-Cookie', $cookie->toHeaderValue());
    }
}

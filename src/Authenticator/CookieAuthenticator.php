<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\Authenticator;

use Authentication\Identifier\IdentifierCollection;
use Authentication\PasswordHasher\PasswordHasherTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * Cookie Authenticator
 *
 * Authenticates an identity based on a cookies data.
 */
class CookieAuthenticator extends AbstractAuthenticator implements PersistenceInterface
{

    use PasswordHasherTrait;

    /**
     * {@inheritDoc}
     */
    protected $_defaultConfig = [
        'rememberMeField' => 'remember_me',
        'fields' => [
            'username' => 'username',
            'password' => 'password'
        ],
        'cookie' => [
            'name' => 'CookieAuth',
            'expire' => null,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httpOnly' => false
        ],
        'passwordHasher' => 'Authentication.Default'
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct(IdentifierCollection $identifiers, array $config = [])
    {
        $this->_checkCakeVersion();

        parent::__construct($identifiers, $config);
    }

    /**
     * Checks the CakePHP Version by looking for the cookie implementation
     *
     * @return void
     */
    protected function _checkCakeVersion()
    {
        if (!class_exists('Cake\Http\Cookie\Cookie')) {
            throw new RuntimeException('Install CakePHP version >=3.5.0 to use the `CookieAuthenticator`.');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(ServerRequestInterface $request, ResponseInterface $response)
    {
        $cookies = $request->getCookieParams();
        $cookieName = $this->getConfig('cookie.name');
        if (!isset($cookies[$cookieName])) {
            return new Result(null, Result::FAILURE_CREDENTIALS_NOT_FOUND, [
                'Login credentials not found'
            ]);
        }
        $token = $cookies[$cookieName];
        list($username, $tokenHash) = explode(':', $token);

        $credentials = [
            'username' => json_decode($username)
        ];
        $identity = $this->identifiers()->identify($credentials);

        if (empty($identity)) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND, $this->identifiers()->getErrors());
        }

        if (!$this->_checkToken($identity, $tokenHash)) {
            return new Result(null, Result::FAILURE_CREDENTIAL_INVALID, [
                'Cookie token does not match'
            ]);
        }

        return new Result($identity, Result::SUCCESS);
    }

    /**
     * {@inheritDoc}
     */
    public function persistIdentity(ServerRequestInterface $request, ResponseInterface $response, $identity)
    {
        $field = $this->getConfig('rememberMeField');
        $bodyData = $request->getParsedBody();

        if (!is_array($bodyData) || empty($bodyData[$field])) {
            return [
                'request' => $request,
                'response' => $response
            ];
        }

        $data = $this->getConfig('cookie');
        $value = $this->_createToken($identity);

        $cookie = new \Cake\Http\Cookie\Cookie(
            $data['name'],
            $value,
            $data['expire'],
            $data['path'],
            $data['domain'],
            $data['secure'],
            $data['httpOnly']
        );

        return [
            'request' => $request,
            'response' => $response->withAddedHeader('Set-Cookie', $cookie->toHeaderValue())
        ];
    }

    /**
     * Creates a plain part of a cookie token.
     *
     * Returns concatenated username and password hash.
     *
     * @param array|\ArrayAccess $identity Identity data.
     * @return string
     */
    protected function _createPlainToken($identity)
    {
        $usernameField = $this->getConfig('fields.username');
        $passwordField = $this->getConfig('fields.password');

        return $identity[$usernameField] . $identity[$passwordField];
    }

    /**
     * Creates a full cookie token.
     *
     * Cookie token consists of concatendated username and hashed username + password hash.
     *
     * @param array|\ArrayAccess $identity Identity data.
     * @return string
     */
    protected function _createToken($identity)
    {
        $plain = $this->_createPlainToken($identity);
        $hash = $this->getPasswordHasher()->hash($plain);

        $usernameField = $this->getConfig('fields.username');

        return json_encode($identity[$usernameField]) . ':' . $hash;
    }

    /**
     * Checks whether a token hash matches the identity data.
     *
     * @param array|\ArrayAccess $identity Identity data.
     * @param string $tokenHash Hashed part of a cookie token.
     * @return string
     */
    protected function _checkToken($identity, $tokenHash)
    {
        $plain = $this->_createPlainToken($identity);

        return $this->getPasswordHasher()->check($plain, $tokenHash);
    }

    /**
     * {@inheritDoc}
     */
    public function clearIdentity(ServerRequestInterface $request, ResponseInterface $response)
    {
        $cookie = (new \Cake\Http\Cookie\Cookie($this->getConfig('cookie.name')))->withExpired();

        return [
            'request' => $request,
            'response' => $response->withAddedHeader('Set-Cookie', $cookie->toHeaderValue())
        ];
    }
}

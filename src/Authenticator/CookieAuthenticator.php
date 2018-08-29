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
namespace Authentication\Authenticator;

use Authentication\Authenticator\Persistence\CookiePersistenceInterface;
use Authentication\Identifier\IdentifierCollectionInterface;
use Authentication\Identifier\IdentifierInterface;
use Authentication\PasswordHasher\PasswordHasherInterface;
use Authentication\UrlChecker\UrlCheckerInterface;
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
    /**
     * Url Checker
     *
     * @var \Authentication\UrlChecker\UrlCheckerInterface
     */
    protected $passwordHasher;

    /**
     * Url Checker
     *
     * @var \Authentication\UrlChecker\UrlCheckerInterface
     */
    protected $urlChecker;

    /**
     * Persistence Implementation
     *
     * @var \Authentication\Authenticator\Persistence\PersistenceInterface
     */
    protected $persistence;

    /**
     * {@inheritDoc}
     */
    protected $defaultConfig = [
        'loginUrl' => null,
        'rememberMeField' => 'remember_me',
        'fields' => [
            IdentifierInterface::CREDENTIAL_USERNAME => 'username',
            IdentifierInterface::CREDENTIAL_PASSWORD => 'password'
        ]
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct(
        IdentifierCollectionInterface $identifiers,
        CookiePersistenceInterface $persistence,
        PasswordHasherInterface $passwordHasher,
        UrlCheckerInterface $urlChecker,
        array $config = []
    ) {
        parent::__construct($identifiers, $config);

        $this->persistence = $persistence;
        $this->passwordHasher = $passwordHasher;
        $this->urlChecker = $urlChecker;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(ServerRequestInterface $request)
    {
        $cookies = $request->getCookieParams();
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

        $identity = $this->_identifier->identify(compact('username'));

        if (empty($identity)) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND, $this->_identifier->getErrors());
        }

        if (!$this->_checkToken($identity, $tokenHash)) {
            return new Result(null, Result::FAILURE_CREDENTIALS_INVALID, [
                'Cookie token does not match'
            ]);
        }

        return new Result($identity, Result::SUCCESS);
    }

    /**
     * {@inheritDoc}
     */
    public function persistIdentity($identity)
    {
        $this->persistence->persistIdentity($identity);
        /*
        $field = $this->config['rememberMeField'];
        $bodyData = $request->getParsedBody();

        if (!$this->_checkUrl($request) || !is_array($bodyData) || empty($bodyData[$field])) {
            return [
                'request' => $request,
                'response' => $response
            ];
        }

        $value = $this->_createToken($identity);
        $cookie = $this->_createCookie($value);

        return [
            'request' => $request,
            'response' => $response->withAddedHeader('Set-Cookie', $cookie->toHeaderValue())
        ];
        */
    }

    /**
     * {@inheritDoc}
     */
    public function clearIdentity()
    {
        $this->persistence->clearIdentity();
        /*
        $cookie = $this->_createCookie(null)->withExpired();

        return [
            'request' => $request,
            'response' => $response->withAddedHeader('Set-Cookie', $cookie->toHeaderValue())
        ];
        */
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
        $usernameField = $this->config['fields.username'];
        $passwordField = $this->config['fields.password'];

        return $identity[$usernameField] . $identity[$passwordField];
    }

    /**
     * Creates a full cookie token serialized as a JSON sting.
     *
     * Cookie token consists of a username and hashed username + password hash.
     *
     * @param array|\ArrayAccess $identity Identity data.
     * @return string
     */
    protected function _createToken($identity)
    {
        $plain = $this->_createPlainToken($identity);
        $hash = $this->passwordHasher->hash($plain);

        $usernameField = $this->config['fields']['username'];

        return json_encode([$identity[$usernameField], $hash]);
    }

    /**
     * Checks whether a token hash matches the identity data.
     *
     * @param array|\ArrayAccess $identity Identity data.
     * @param string $tokenHash Hashed part of a cookie token.
     * @return bool
     */
    protected function _checkToken($identity, $tokenHash)
    {
        $plain = $this->_createPlainToken($identity);

        return $this->getPasswordHasher()->check($plain, $tokenHash);
    }
}

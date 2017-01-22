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
namespace ADmad\JwtAuth\Auth;

use Authentication\Identifier\TokenIdentifier;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\Entity;
use Exception;
use Firebase\JWT\JWT;
use stdClass;

/**
 * An authentication adapter for authenticating using JSON Web Tokens.
 *
 * @see http://jwt.io
 * @see http://tools.ietf.org/html/draft-ietf-oauth-json-web-token
 */
class JwtTokenIdentifier extends TokenIdentifier
{

    /**
     * Default settings
     *
     * - `prefix` - Token prefix. Defaults to `'bearer'`.
     * - `allowedAlgs` - List of supported verification algorithms.
     *   Defaults to ['HS256']. See API of JWT::decode() for more info.
     * - `tokenVerification` - Boolean indicating whether the `sub` claim of JWT
     *   token should be used to query the user model and get user record. If
     *   set to `false` JWT's payload is directly retured. Defaults to `true`.
     * - `model` - The model name of users, defaults to `Users`.
     * - `fields` - Key `username` denotes the identifier field for fetching user
     *   record. The `sub` claim of JWT must contain identifier value.
     *   Defaults to ['username' => 'id'].
     * - `finder` - Finder method.
     * - `unauthenticatedException` - Fully namespaced exception name. Exception to
     *   throw if authentication fails. Set to false to do nothing.
     *   Defaults to '\Cake\Network\Exception\UnauthorizedException'.
     * - `key` - The key, or map of keys used to decode JWT. If not set, value
     *   of Security::salt() will be used.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'tokenField' => 'token',
        'model' => 'Users',
        'finder' => 'all',
        'tokenVerification' => false,
        'tokenPrefix' => 'bearer',
        'allowedAlgs' => ['HS256'],
        'queryDatasource' => true,
        'fields' => [
            'username' => 'id'
        ],
        'unauthenticatedException' => '\Cake\Network\Exception\UnauthorizedException',

        'key' => null,
        'salt' => null,
        'debug' => false
    ];

    /**
     * Identify
     *
     * @param array $data Authentication credentials
     * @return false|EntityInterface
     */
    public function identify($data)
    {
        if (!isset($data['token'])) {
            return false;
        }

        $result = $this->_decode($data['token']);
        if (!$result instanceof stdClass) {
            return false;
        }

        $tokenVerification = $this->config('tokenVerification');
        $result = json_decode(json_encode($result), true);

        if ($tokenVerification === false) {
            return new Entity($result);
        }

        if ($tokenVerification === true) {
            $tokenVerification = 'Orm';
        }

        $this->config('tokenField', $this->config('fields')['username']);
        return $this->_dispatchTokenVerification($tokenVerification, $result[$this->config('fields')['username']]);
    }

    /**
     * Decode JWT token.
     *
     * @param string $token JWT token to decode.
     *
     * @return object|null The JWT's payload as a PHP object, null on failure.
     */
    protected function _decode($token)
    {
        $config = $this->_config;

        $token = str_ireplace($config['tokenPrefix'] . ' ', '', $token);

        try {
            $payload = JWT::decode($token, $config['key'] ?: $config['salt'], $config['allowedAlgs']);

            return $payload;
        } catch (Exception $e) {
            if ($config['debug']) {
                throw $e;
            }
        }
    }

    /**
     * Handles an unauthenticated access attempt. Depending on value of config
     * `unauthenticatedException` either throws the specified exception or returns
     * null.
     *
     * @param \Cake\Network\Request $request A request object.
     * @param \Cake\Network\Response $response A response object.
     *
     * @throws \Cake\Network\Exception\UnauthorizedException Or any other
     *   configured exception.
     *
     * @return void
     */
    public function unauthenticated(Request $request, Response $response)
    {
        if (!$this->_config['unauthenticatedException']) {
            return;
        }

        $message = $this->_error ? $this->_error->getMessage() : $this->_registry->Auth->_config['authError'];

        $exception = new $this->_config['unauthenticatedException']($message);
        throw $exception;
    }
}

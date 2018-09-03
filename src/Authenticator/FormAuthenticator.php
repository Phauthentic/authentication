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

use Authentication\Identifier\IdentifierInterface;
use Authentication\UrlChecker\UrlCheckerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Form Authenticator
 *
 * Authenticates an identity based on the POST data of the request.
 */
class FormAuthenticator extends AbstractAuthenticator
{
    /**
     * URL Checker
     *
     * @var \Authentication\UrlChecker\UrlCheckerInterface
     */
    protected $urlChecker;

    /**
     * Default config for this object.
     * - `fields` The fields to use to identify a user by.
     * - `loginUrl` Login URL or an array of URLs.
     * - `urlChecker` Url checker config.
     *
     * @var array
     */
    protected $defaultConfig = [
        'loginUrl' => null,
        'fields' => [
            IdentifierInterface::CREDENTIAL_USERNAME => 'username',
            IdentifierInterface::CREDENTIAL_PASSWORD => 'password'
        ]
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct(
        IdentifierCollection $identifiers,
        UrlCheckerInterface $urlChecker,
        array $config = []
    ) {
        parent::__construct($identifiers, $config);

        $this->urlChecker = $urlChecker;
    }

    /**
     * Checks the fields to ensure they are supplied.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request that contains login information.
     * @return array|null Username and password retrieved from a request body.
     */
    protected function getData(ServerRequestInterface $request)
    {
        $fields = $this->config['fields'];
        $body = (array)$request->getParsedBody();

        $data = [];
        foreach ($fields as $key => $field) {
            if (!isset($body[$field])) {
                return null;
            }

            $value = $body[$field];
            if (!is_string($value) || !strlen($value)) {
                return null;
            }

            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * Prepares the error object for a login URL error
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request that contains login information.
     * @return \Authentication\Authenticator\ResultInterface
     */
    protected function _buildLoginUrlErrorResult($request)
    {
        $errors = [
            sprintf(
                'Login URL `%s` did not match `%s`.',
                (string)$request->getUri(),
                implode('` or `', (array)$this->config['loginUrl'])
            )
        ];

        return new Result(null, Result::FAILURE_OTHER, $errors);
    }

    /**
     * Authenticates the identity contained in a request. Will use the `config.userModel`, and `config.fields`
     * to find POST data that is used to find a matching record in the `config.userModel`. Will return false if
     * there is no post data, either username or password is missing, or if the scope conditions have not been met.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request that contains login information.
     * @param \Psr\Http\Message\ResponseInterface $response Unused response object.
     * @return \Authentication\Authenticator\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request)
    {
        if (!$this->urlChecker->check($request, $this->config['loginUrl'])) {
            return $this->_buildLoginUrlErrorResult($request);
        }

        $data = $this->getData($request);
        if ($data === null) {
            return new Result(null, Result::FAILURE_CREDENTIALS_MISSING, [
                'Login credentials not found'
            ]);
        }

        $user = $this->_identifier->identify($data);

        if (empty($user)) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND, $this->_identifier->getErrors());
        }

        return new Result($user, Result::SUCCESS);
    }
}

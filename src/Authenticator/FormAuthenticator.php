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
use Psr\Http\Message\ServerRequestInterface;

/**
 * Form Authenticator
 *
 * Authenticates an identity based on the POST data of the request.
 */
class FormAuthenticator extends AbstractAuthenticator
{

    use CredentialFieldsTrait;

    /**
     * URL Checker
     *
     * @var \Authentication\UrlChecker\UrlCheckerInterface
     */
    protected $urlChecker;

    /**
     * URLs to check for the login credentials
     *
     * @var array
     */
    protected $loginUrls = [];

    /**
     * {@inheritDoc}
     */
    public function __construct(
        IdentifierInterface $identifier,
        UrlCheckerInterface $urlChecker
    ) {
        parent::__construct($identifier);
        $this->urlChecker = $urlChecker;
    }

    /**
     * Sets multiple login URLs.
     *
     * @param array $urls An array of URLs.
     * @return $this
     */
    public function setLoginUrls(array $urls): self
    {
        $this->loginUrls = $urls;

        return $this;
    }

    /**
     * Adds a login URL.
     *
     * @param string $url Login URL.
     * @return $this
     */
    public function addLoginUrl(string $url): self
    {
        $this->loginUrls[] = $url;

        return $this;
    }

    /**
     * Checks the fields to ensure they are supplied.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request that contains login information.
     * @return array|null Username and password retrieved from a request body.
     */
    protected function getData(ServerRequestInterface $request): ?array
    {
        $body = (array)$request->getParsedBody();

        $data = [];
        foreach ($this->credentialFields as $key => $field) {
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
    protected function _buildLoginUrlErrorResult($request): ResultInterface
    {
        $errors = [
            sprintf(
                'Login URL `%s` did not match `%s`.',
                (string)$request->getUri(),
                implode('` or `', $this->loginUrls)
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
     * @return \Authentication\Authenticator\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request): ResultInterface
    {
        if (!$this->urlChecker->check($request, $this->loginUrls)) {
            return $this->_buildLoginUrlErrorResult($request);
        }

        $data = $this->getData($request);
        if ($data === null) {
            return new Result(null, Result::FAILURE_CREDENTIALS_MISSING, [
                'Login credentials not found'
            ]);
        }

        $user = $this->identifier->identify($data);

        if (empty($user)) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND, $this->identifier->getErrors());
        }

        return new Result($user, Result::SUCCESS);
    }
}

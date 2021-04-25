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

namespace Phauthentic\Authentication\Authenticator;

use Phauthentic\Authentication\Identifier\IdentifierInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Token Authenticator
 *
 * Authenticates an identity based on a token in a query param or the header.
 */
class TokenAuthenticator extends AbstractAuthenticator implements StatelessInterface
{
    /**
     * Query param
     *
     * @var null|string
     */
    protected ?string $queryParam = null;

    /**
     * Header
     *
     * @var null|string
     */
    protected ?string $header = null;

    /**
     * Token Prefix
     *
     * @var null|string
     */
    protected ?string $tokenPrefix = null;

    /**
     * Sets the header name to get the token from
     *
     * @param null|string $name Name
     * @return $this
     */
    public function setHeaderName(?string $name): self
    {
        $this->header = $name;

        return $this;
    }

    /**
     * Sets the query param to get the token from
     *
     * @param null|string $queryParam Query param
     * @return $this
     */
    public function setQueryParam(?string $queryParam): self
    {
        $this->queryParam = $queryParam;

        return $this;
    }

    /**
     * Sets the token prefix
     *
     * @param null|string $prefix Token prefix
     * @return $this
     */
    public function setTokenPrefix(?string $prefix): self
    {
        $this->tokenPrefix = $prefix;

        return $this;
    }

    /**
     * Checks if the token is in the headers or a request parameter
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request that contains login information.
     * @return string|null
     */
    protected function getToken(ServerRequestInterface $request): ?string
    {
        $token = $this->getTokenFromHeader($request, $this->header);
        if ($token === null) {
            $token = $this->getTokenFromQuery($request, $this->queryParam);
        }

        $prefix = $this->tokenPrefix;
        if ($prefix !== null && is_string($token)) {
            return $this->stripTokenPrefix($token, $prefix);
        }

        return $token;
    }

    /**
     * Strips a prefix from a token
     *
     * @param string $token Token string
     * @param string $prefix Prefix to strip
     * @return string
     */
    protected function stripTokenPrefix(string $token, string $prefix): string
    {
        return str_ireplace($prefix . ' ', '', $token);
    }

    /**
     * Gets the token from the request headers
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request that contains login information.
     * @param string|null $headerLine Header name
     * @return string|null
     */
    protected function getTokenFromHeader(ServerRequestInterface $request, ?string $headerLine): ?string
    {
        if (!empty($headerLine)) {
            $header = $request->getHeaderLine($headerLine);
            if (!empty($header)) {
                return $header;
            }
        }

        return null;
    }

    /**
     * Gets the token from the request headers
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request that contains login information.
     * @param string|null $queryParam Request query parameter name
     * @return string|null
     */
    protected function getTokenFromQuery(ServerRequestInterface $request, ?string $queryParam): ?string
    {
        $queryParams = $request->getQueryParams();

        if (empty($queryParams[$queryParam])) {
            return null;
        }

        return $queryParams[$queryParam];
    }

    /**
     * Authenticates the identity contained in a request. Will use the `config.userModel`, and `config.fields`
     * to find POST data that is used to find a matching record in the `config.userModel`. Will return false if
     * there is no post data, either username or password is missing, or if the scope conditions have not been met.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request that contains login information.
     * @return \Phauthentic\Authentication\Authenticator\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request): ResultInterface
    {
        $token = $this->getToken($request);
        if ($token === null) {
            return new Result(null, Result::FAILURE_CREDENTIALS_MISSING);
        }

        $user = $this->identifier->identify([
            IdentifierInterface::CREDENTIAL_TOKEN => $token
        ]);

        if (empty($user)) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND, $this->identifier->getErrors());
        }

        return new Result($user, Result::SUCCESS);
    }

    /**
     * No-op method.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request A request object.
     * @return void
     */
    public function unauthorizedChallenge(ServerRequestInterface $request): void
    {
    }
}

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

namespace Phauthentic\Authentication\UrlChecker;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Checks if a request object contains a valid URL using regular expression
 */
class RegexUrlChecker implements UrlCheckerInterface
{
    /**
     * @var bool
     */
    protected bool $checkFullUrl = false;

    /**
     * Check the full URL
     *
     * @param bool $fullUrl Full URL to check or not
     * @return $this
     */
    public function checkFullUrl(bool $fullUrl): self
    {
        $this->checkFullUrl = $fullUrl;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function check(ServerRequestInterface $request, string $regex): bool
    {
        $requestUrl = $this->getUrlFromRequest($request->getUri());

        return (bool)preg_match($regex, $requestUrl);
    }

    /**
     * Returns current url.
     *
     * @param \Psr\Http\Message\UriInterface $uri Server Request
     * @return string
     */
    protected function getUrlFromRequest(UriInterface $uri): string
    {
        if ($this->checkFullUrl) {
            return (string)$uri;
        }

        return $uri->getPath();
    }
}

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

use Phauthentic\Authentication\UrlChecker\UrlCheckerInterface;
use Psr\Http\Message\ServerRequestInterface;

trait UrlAwareTrait
{
    /**
     * Url Checker
     *
     * @var \Phauthentic\Authentication\UrlChecker\UrlCheckerInterface
     */
    protected UrlCheckerInterface $urlChecker;

    /**
     * Login URLs
     *
     * @var string[]
     */
    protected array $loginUrls = [];

    /**
     * Sets multiple login URLs.
     *
     * @param array<int, string> $urls An array of URLs.
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
     * URL checker wrapper for multiple URLs.
     * Returns true if there are no URLs configured.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request.
     * @return bool
     */
    protected function checkUrl(ServerRequestInterface $request): bool
    {
        if (!$this->loginUrls) {
            return true;
        }

        foreach ($this->loginUrls as $url) {
            if ($this->urlChecker->check($request, $url)) {
                return true;
            }
        }

        return false;
    }
}

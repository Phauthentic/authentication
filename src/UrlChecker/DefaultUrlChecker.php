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
namespace Authentication\UrlChecker;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Checks if a request object contains a valid URL
 */
class DefaultUrlChecker implements UrlCheckerInterface
{

    protected $checkFullUrl = false;

    protected $useRegex = false;

    /**
     * Use regex to check the URL
     *
     * @param bool $useRegex Use regex or not
     * @return $this
     */
    public function useRegex(bool $useRegex): self
    {
        $this->useRegex = $useRegex;

        return $this;
    }

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
    public function check(ServerRequestInterface $request, $urls, array $options = [])
    {
        $options = array_merge([
            'checkFullUrl' => $this->checkFullUrl,
            'useRegex' => $this->useRegex
        ], $options);

        $urls = (array)$urls;

        if (empty($urls)) {
            return true;
        }

        $checker = $this->_getChecker($options);

        $url = $this->_getUrlFromRequest($request->getUri(), $options['checkFullUrl']);

        foreach ($urls as $validUrl) {
            if ($checker($validUrl, $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the checker function name or a callback
     *
     * @param array $options Array of options
     * @return string|callable
     */
    protected function _getChecker(array $options = [])
    {
        if (isset($options['useRegex']) && $options['useRegex']) {
            return 'preg_match';
        }

        return function ($validUrl, $url) {
            return $validUrl === $url;
        };
    }

    /**
     * Returns current url.
     *
     * @param \Psr\Http\Message\UriInterface $uri Server Request
     * @param bool $getFullUrl Get the full URL or just the path
     * @return string
     */
    protected function _getUrlFromRequest(UriInterface $uri, $getFullUrl = false)
    {
        if ($getFullUrl) {
            return (string)$uri;
        }

        return $uri->getPath();
    }
}

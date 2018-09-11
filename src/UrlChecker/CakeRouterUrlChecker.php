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

use Cake\Routing\Router;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Checks if a request object contains a valid URL
 */
class CakeRouterUrlChecker extends DefaultUrlChecker
{

    /**
     * {@inheritdoc}
     */
    public function check(ServerRequestInterface $request, $loginUrls): bool
    {
        $url = $this->_getUrlFromRequest($request->getUri());

        if (!is_array($loginUrls) || empty($loginUrls)) {
            throw new InvalidArgumentException('The $loginUrls parameter is empty or not of type array.');
        }

        // If it's a single route array add to another
        if (!is_numeric(key($loginUrls))) {
            $loginUrls = [$loginUrls];
        }

        foreach ($loginUrls as $validUrl) {
            $validUrl = Router::url($validUrl, $this->checkFullUrl);

            if ($validUrl === $url) {
                return true;
            }
        }

        return false;
    }
}

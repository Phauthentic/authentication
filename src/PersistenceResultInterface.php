<?php
declare(strict_types=1);
/**
 * Copyright (c) Phauthentic (https://github.com/Phauthentic)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Phauthentic (https://github.com/Phauthentic)
 * @link          https://github.com/Phauthentic
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Phauthentic\Authentication;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Persistence Result Interface
 */
interface PersistenceResultInterface
{
    /**
     * Returns response.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse(): ResponseInterface;

    /**
     * Returns request.
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface;
}

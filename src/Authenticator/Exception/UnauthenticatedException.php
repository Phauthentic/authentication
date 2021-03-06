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

namespace Phauthentic\Authentication\Authenticator\Exception;

use RuntimeException;
use Throwable;

/**
 * An exception that signals that authentication was required but missing.
 */
class UnauthenticatedException extends RuntimeException implements AuthenticationExceptionInterface
{
    /**
     * {@inheritDoc}
     */
    public function __construct(string $message = '', int $code = 401, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

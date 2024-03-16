<?php

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

declare(strict_types=1);

namespace Phauthentic\Authentication\Authenticator;

interface FailureInterface
{
    /**
     * Returns failed authenticator.
     *
     * @return AuthenticatorInterface
     */
    public function getAuthenticator(): AuthenticatorInterface;

    /**
     * Returns failed result.
     *
     * @return ResultInterface
     */
    public function getResult(): ResultInterface;
}

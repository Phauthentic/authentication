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

namespace Phauthentic\Authentication\Identifier;

use ArrayAccess;

interface IdentifierInterface
{
    public const CREDENTIAL_USERNAME = 'username';

    public const CREDENTIAL_PASSWORD = 'password';

    public const CREDENTIAL_TOKEN = 'token';

    public const CREDENTIAL_JWT_SUBJECT = 'sub';

    /**
     * Identifies an user or service by the passed credentials
     *
     * @param array<string, string> $credentials Authentication credentials
     * @return \ArrayAccess|null
     */
    public function identify(array $credentials): ?ArrayAccess;

    /**
     * Gets a list of errors happened in the identification process
     *
     * @return array<mixed, mixed>
     */
    public function getErrors(): array;
}

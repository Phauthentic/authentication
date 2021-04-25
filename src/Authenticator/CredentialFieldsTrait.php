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

/**
 * Sets the user credential fields
 */
trait CredentialFieldsTrait
{
    /**
     * Credential fields
     *
     * @var array<string, string>
     */
    protected array $credentialFields = [
        IdentifierInterface::CREDENTIAL_USERNAME => 'username',
        IdentifierInterface::CREDENTIAL_PASSWORD => 'password'
    ];

    /**
     * Set the fields used to to get the credentials from
     *
     * @param string $username Username field
     * @param string $password Password field
     * @return $this
     */
    public function setCredentialFields(string $username, string $password): self
    {
        $this->credentialFields[IdentifierInterface::CREDENTIAL_USERNAME] = $username;
        $this->credentialFields[IdentifierInterface::CREDENTIAL_PASSWORD] = $password;

        return $this;
    }
}

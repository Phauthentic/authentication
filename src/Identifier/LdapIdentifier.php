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

declare(strict_types=1);

namespace Phauthentic\Authentication\Identifier;

use ArrayAccess;
use ArrayObject;
use Phauthentic\Authentication\Identifier\Ldap\AdapterInterface;
use ErrorException;

/**
 * LDAP Identifier
 *
 * Identifies authentication credentials using LDAP.
 *
 * ```
 *  $identifier = (new LadapIdentifier($ldapAdapter, 'ldap.example.com', function($username) {
 *         return $username; //transform into a rdn or dn
 *     })
 *      ->setOptions([
 *         LDAP_OPT_PROTOCOL_VERSION => 3
 *     ]);
 * ```
 *
 * @link https://github.com/QueenCityCodeFactory/LDAP
 */
class LdapIdentifier extends AbstractIdentifier
{
    /**
     * Credential fields
     *
     * @var array<string, string>
     */
    protected array $credentialFields = [
        self::CREDENTIAL_USERNAME => 'username',
        self::CREDENTIAL_PASSWORD => 'password'
    ];

    /**
     * Host
     *
     * @var string
     */
    protected string $host = '';

    /**
     * Bind DN
     *
     * @var callable
     */
    protected $bindDN;

    /**
     * Port
     *
     * @var int
     */
    protected int $port = 389;

    /**
     * Adapter Options
     *
     * @var array<int, bool|int|string>
     */
    protected array $ldapOptions = [];

    /**
     * List of errors
     *
     * @var array<int, string>
     */
    protected array $errors = [];

    /**
     * LDAP connection object
     *
     * @var \Phauthentic\Authentication\Identifier\Ldap\AdapterInterface
     */
    protected AdapterInterface $ldap;

    /**
     * {}
     * @param \Phauthentic\Authentication\Identifier\Ldap\AdapterInterface $ldapAdapter
     * @param string $host
     * @param callable $bindDN
     * @param int $port
     */
    public function __construct(AdapterInterface $ldapAdapter, string $host, callable $bindDN, int $port = 389)
    {
        $this->ldap = $ldapAdapter;
        $this->bindDN = $bindDN;
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Set the fields used to to get the credentials from
     *
     * @param string $username Username field
     * @param string $password Password field
     * @return $this
     */
    public function setCredentialFields(string $username, string $password): self
    {
        $this->credentialFields[self::CREDENTIAL_USERNAME] = $username;
        $this->credentialFields[self::CREDENTIAL_PASSWORD] = $password;

        return $this;
    }

    /**
     * Sets LDAP options
     *
     * @param array<int, bool|int|string> $options LDAP Options array
     * @return $this
     */
    public function setLdapOptions(array $options): self
    {
        $this->ldapOptions = $options;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function identify(array $data): ?ArrayAccess
    {
        $this->connectLdap();
        $fields = $this->credentialFields;

        if (isset($data[$fields[self::CREDENTIAL_USERNAME]]) && isset($data[$fields[self::CREDENTIAL_PASSWORD]])) {
            return $this->bindUser($data[$fields[self::CREDENTIAL_USERNAME]], $data[$fields[self::CREDENTIAL_PASSWORD]]);
        }

        return null;
    }

    /**
     * Returns configured LDAP adapter.
     *
     * @return \Phauthentic\Authentication\Identifier\Ldap\AdapterInterface
     */
    public function getAdapter(): AdapterInterface
    {
        return $this->ldap;
    }

    /**
     * Initializes the LDAP connection
     *
     * @return void
     */
    protected function connectLdap()
    {
        $this->ldap->connect(
            $this->host,
            $this->port,
            $this->ldapOptions
        );
    }

    /**
     * Try to bind the given user to the LDAP server
     *
     * @param string $username The username
     * @param string $password The password
     * @return \ArrayAccess|null
     */
    protected function bindUser($username, $password)
    {
        try {
            $callable = $this->bindDN;
            $ldapBind = $this->ldap->bind($callable($username), $password);
            if ($ldapBind === true) {
                $this->ldap->unbind();

                return new ArrayObject([
                    $this->credentialFields[self::CREDENTIAL_USERNAME] => $username
                ]);
            }
        } catch (ErrorException $e) {
            $this->handleLdapError($e->getMessage());
        }
        $this->ldap->unbind();

        return null;
    }

    /**
     * Handles an LDAP error
     *
     * @param string $message Exception message
     * @return void
     */
    protected function handleLdapError($message)
    {
        $extendedError = $this->ldap->getDiagnosticMessage();
        if (!is_null($extendedError)) {
            $this->errors[] = $extendedError;
        }
        $this->errors[] = $message;
    }
}

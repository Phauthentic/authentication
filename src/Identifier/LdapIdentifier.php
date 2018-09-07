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
namespace Authentication\Identifier;

use ArrayObject;
use Authentication\Identifier\Ldap\AdapterInterface;
use Authentication\Identifier\Ldap\ExtensionAdapter;
use Cake\Core\App;
use ErrorException;
use InvalidArgumentException;
use RuntimeException;

/**
 * LDAP Identifier
 *
 * Identifies authentication credentials using LDAP.
 *
 * ```
 *  new LdapIdentifier([
 *       'host' => 'ldap.example.com',
 *       'bindDN' => function($username) {
 *           return $username; //transform into a rdn or dn
 *       },
 *       'options' => [
 *           LDAP_OPT_PROTOCOL_VERSION => 3
 *       ]
 *  ]);
 * ```
 *
 * @link https://github.com/QueenCityCodeFactory/LDAP
 */
class LdapIdentifier extends AbstractIdentifier
{
    /**
     * Default configuration
     *
     * @var array
     */
    protected $defaultConfig = [
        'ldap' => ExtensionAdapter::class,
        'fields' => [
            self::CREDENTIAL_USERNAME => 'username',
            self::CREDENTIAL_PASSWORD => 'password'
        ],
        'port' => 389,
        'options' => []
    ];

    /**
     * Credential fields
     *
     * @var array
     */
    protected $credentialFields = [
        self::CREDENTIAL_USERNAME => 'username',
        self::CREDENTIAL_PASSWORD => 'password'
    ];

    /**
     * Port
     *
     * @var int
     */
    protected $port = 389;

    /**
     * Adapter Options
     *
     * @var array
     */
    protected $options = [];

    protected $config = [];

    /**
     * List of errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * LDAP connection object
     *
     * @var \Authentication\Identifier\Ldap\AdapterInterface
     */
    protected $ldap = null;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->defaultConfig, $config);
        $this->_checkLdapConfig();
        $this->_buildLdapObject();
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
     * Checks the LDAP config
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function _checkLdapConfig()
    {
        if (!isset($this->config['bindDN'])) {
            throw new RuntimeException('Config `bindDN` is not set.');
        }
        if (!is_callable($this->config['bindDN'])) {
            throw new InvalidArgumentException(sprintf(
                'The `bindDN` config is not a callable. Got `%s` instead.',
                gettype($this->config['bindDN'])
            ));
        }
        if (!isset($this->config['host'])) {
            throw new RuntimeException('Config `host` is not set.');
        }
    }

    /**
     * Constructs the LDAP object and sets it to the property
     *
     * @throws \RuntimeException
     * @return void
     */
    protected function _buildLdapObject()
    {
        $ldap = $this->config['ldap'];

        if (is_string($ldap)) {
            $ldap = new $ldap();
        }

        if (!($ldap instanceof AdapterInterface)) {
            $message = sprintf('Option `ldap` must implement `%s`.', AdapterInterface::class);
            throw new RuntimeException($message);
        }

        $this->ldap = $ldap;
    }

    /**
     * {@inheritDoc}
     */
    public function identify(array $data)
    {
        $this->_connectLdap();
        $fields = $this->config['fields'];

        if (isset($data[$fields[self::CREDENTIAL_USERNAME]]) && isset($data[$fields[self::CREDENTIAL_PASSWORD]])) {
            return $this->_bindUser($data[$fields[self::CREDENTIAL_USERNAME]], $data[$fields[self::CREDENTIAL_PASSWORD]]);
        }

        return null;
    }

    /**
     * Returns configured LDAP adapter.
     *
     * @return \Authentication\Identifier\Ldap\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->ldap;
    }

    /**
     * Initializes the LDAP connection
     *
     * @return void
     */
    protected function _connectLdap()
    {
        $config = $this->config;

        $this->ldap->connect(
            $config['host'],
            $config['port'],
            $this->config['options']
        );
    }

    /**
     * Try to bind the given user to the LDAP server
     *
     * @param string $username The username
     * @param string $password The password
     * @return \ArrayAccess|null
     */
    protected function _bindUser($username, $password)
    {
        try {
            $ldapBind = $this->ldap->bind($this->config['bindDN']($username), $password);
            if ($ldapBind === true) {
                $this->ldap->unbind();

                return new ArrayObject([
                    $this->config['fields'][self::CREDENTIAL_USERNAME] => $username
                ]);
            }
        } catch (ErrorException $e) {
            $this->_handleLdapError($e->getMessage());
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
    protected function _handleLdapError($message)
    {
        $extendedError = $this->ldap->getDiagnosticMessage();
        if (!is_null($extendedError)) {
            $this->errors[] = $extendedError;
        }
        $this->errors[] = $message;
    }
}

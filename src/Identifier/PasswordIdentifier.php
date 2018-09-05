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

use Authentication\Identifier\Resolver\ResolverInterface;
use Authentication\PasswordHasher\PasswordHasherInterface;

/**
 * Password Identifier
 *
 * Identifies authentication credentials with password
 *
 * ```
 *  new PasswordIdentifier([
 *      'fields' => [
 *          'username' => ['username', 'email'],
 *          'password' => 'password'
 *      ]
 *  ]);
 * ```
 *
 * When configuring PasswordIdentifier you can pass in config to which fields,
 * model and additional conditions are used.
 */
class PasswordIdentifier extends AbstractIdentifier
{

    /**
     * Resolver
     *
     * @var \Authentication\Identifier\Resolver\ResolverInterface
     */
    protected $resolver;

    /**
     * Password Hasher
     *
     * @var \Authentication\PasswordHasher\PasswordHasherInterface
     */
    protected $passwordHasher;

    /**
     * Whether or not the user authenticated by this class
     * requires their password to be rehashed with another algorithm.
     *
     * @var bool
     */
    protected $needsPasswordRehash = false;

    /**
     * Default configuration.
     * - `fields` The fields to use to identify a user by:
     *   - `username`: one or many username fields.
     *   - `password`: password field.
     *
     * @var array
     */
    protected $defaultConfig = [
        'fields' => [
            self::CREDENTIAL_USERNAME => 'username',
            self::CREDENTIAL_PASSWORD => 'password'
        ]
    ];

    protected $config = [];

    /**
     * Constructor
     *
     * @param array $config Configuration
     */
    public function __construct(
        ResolverInterface $resolver,
        PasswordHasherInterface $passwordHasher,
        array $config = []
    ) {
        $this->config = array_merge_recursive($this->defaultConfig, $config);
        $this->resolver = $resolver;
        $this->passwordHasher = $passwordHasher;
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
        $this->config['fields'][self::CREDENTIAL_USERNAME] = $username;
        $this->config['fields'][self::CREDENTIAL_PASSWORD] = $password;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function identify(array $data)
    {
        if (!isset($data[self::CREDENTIAL_USERNAME])) {
            return null;
        }

        $identity = $this->_findIdentity($data[self::CREDENTIAL_USERNAME]);
        if (array_key_exists(self::CREDENTIAL_PASSWORD, $data)) {
            $password = $data[self::CREDENTIAL_PASSWORD];
            if (!$this->_checkPassword($identity, $password)) {
                return null;
            }
        }

        return $identity;
    }

    /**
     * Find a user record using the username and password provided.
     * Input passwords will be hashed even when a user doesn't exist. This
     * helps mitigate timing attacks that are attempting to find valid usernames.
     *
     * @param array|\ArrayAccess|null $identity The identity or null.
     * @param string|null $password The password.
     * @return bool
     */
    protected function _checkPassword($identity, $password)
    {
        $passwordField = $this->config['fields'][self::CREDENTIAL_PASSWORD];

        if ($identity === null) {
            $identity = [
                $passwordField => ''
            ];
        }

        $hasher = $this->passwordHasher;
        $hashedPassword = $identity[$passwordField];
        if (!$hasher->check($password, $hashedPassword)) {
            return false;
        }

        $this->needsPasswordRehash = $hasher->needsRehash($hashedPassword);

        return true;
    }

    /**
     * Check if a password needs to be re-hashed
     *
     * @return bool
     */
    public function needsPasswordRehash()
    {
        return $this->needsPasswordRehash;
    }

    /**
     * Find a user record using the username/identifier provided.
     *
     * @param string $identifier The username/identifier.
     * @return \ArrayAccess|array|null
     */
    protected function _findIdentity($identifier)
    {
        $fields = $this->config['fields'][self::CREDENTIAL_USERNAME];
        $conditions = [];
        foreach ((array)$fields as $field) {
            $conditions[$field] = $identifier;
        }

        return $this->resolver->find($conditions, ResolverInterface::TYPE_OR);
    }
}

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
 * Identifies authentication credentials with password.
 *
 * When configuring PasswordIdentifier you can pass in config to which fields,
 * model and additional conditions are used.
 */
class PasswordIdentifier extends AbstractIdentifier
{

    /**
     * Credential fields
     *
     * @var array
     */
    protected $credentialFields = [
        IdentifierInterface::CREDENTIAL_USERNAME => 'username',
        IdentifierInterface::CREDENTIAL_PASSWORD => 'password'
    ];

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
     * Constructor
     *
     * @param ResolverInterface $resolver Resolver instance.
     * @param PasswordHasherInterface $passwordHasher Password hasher.
     */
    public function __construct(
        ResolverInterface $resolver,
        PasswordHasherInterface $passwordHasher
    ) {
        $this->resolver = $resolver;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Set the username fields used to to get the credentials from.
     *
     * @param array $usernames An array of fields.
     * @return $this
     */
    public function setUsernameFields(array $usernames): self
    {
        $this->credentialFields[self::CREDENTIAL_USERNAME] = $usernames;

        return $this;
    }

    /**
     * Set the single username field used to to get the credentials from.
     *
     * @param string $username Username field.
     * @return $this
     */
    public function setUsernameField(string $username): self
    {
        return $this->setUsernameFields([$username]);
    }

    /**
     * Sets the password field.
     *
     * @param string $password Password field.
     * @return $this
     */
    public function setPasswordField(string $password): self
    {
        $this->credentialFields[self::CREDENTIAL_PASSWORD] = $password;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function identify(array $credentials)
    {
        if (!isset($credentials[self::CREDENTIAL_USERNAME])) {
            return null;
        }

        $data = $this->_findIdentity($credentials[self::CREDENTIAL_USERNAME]);
        if (array_key_exists(self::CREDENTIAL_PASSWORD, $credentials)) {
            $password = $credentials[self::CREDENTIAL_PASSWORD];
            if (!$this->_checkPassword($data, $password)) {
                return null;
            }
        }

        return $data;
    }

    /**
     * Find a user record using the username and password provided.
     * Input passwords will be hashed even when a user doesn't exist. This
     * helps mitigate timing attacks that are attempting to find valid usernames.
     *
     * @param array|\ArrayAccess|null $data The identity or null.
     * @param string|null $password The password.
     * @return bool
     */
    protected function _checkPassword($data, $password): bool
    {
        $passwordField = $this->credentialFields[self::CREDENTIAL_PASSWORD];

        if ($data === null) {
            $data = [
                $passwordField => ''
            ];
        }

        $hasher = $this->passwordHasher;
        $hashedPassword = $data[$passwordField];
        if (!$hasher->check((string)$password, $hashedPassword)) {
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
    public function needsPasswordRehash(): bool
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
        $fields = $this->credentialFields[self::CREDENTIAL_USERNAME];
        $conditions = [];
        foreach ((array)$fields as $field) {
            $conditions[$field] = $identifier;
        }

        return $this->resolver->find($conditions, ResolverInterface::TYPE_OR);
    }
}

<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\PasswordHasher;

use Cake\Core\Configure;
use Cake\Error\Debugger;
use Cake\Utility\Security;
use RuntimeException;

/**
 * Password hashing class that use weak hashing algorithms. This class is
 * intended only to be used with legacy databases where passwords have
 * not been migrated to a stronger algorithm yet.
 */
class LegacyPasswordHasher extends AbstractPasswordHasher
{

    /**
     * Hash type
     *
     * @var string
     */
    protected $hashType;

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        if (Configure::read('debug')) {
            Debugger::checkSecurityKeys();
        }
        if (!class_exists(Security::class)) {
            throw new RuntimeException('You must install the cakephp/utility dependency to use this password hasher');
        }
    }

    /**
     * Sets the hash type
     *
     * @param string $type Hashing algo to use. Valid values are those supported by `$algo` argument of `password_hash()`. Defaults to `PASSWORD_DEFAULT`
     * @return $this
     */
    public function setHashType(string $type): self
    {
        $this->hashType = $type;

        return $this;
    }

    /**
     * Generates password hash.
     *
     * @param string $password Plain text password to hash.
     * @return string Password hash
     */
    public function hash($password): string
    {
        return Security::hash($password, $this->hashType, true);
    }

    /**
     * Check hash. Generate hash for user provided password and check against existing hash.
     *
     * @param string $password Plain text password to hash.
     * @param string $hashedPassword Existing hashed password.
     * @return bool True if hashes match else false.
     */
    public function check($password, string $hashedPassword): bool
    {
        return $hashedPassword === $this->hash($password);
    }
}

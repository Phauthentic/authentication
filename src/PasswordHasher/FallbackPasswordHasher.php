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

/**
 * A password hasher that can use multiple different hashes where only
 * one is the preferred one. This is useful when trying to migrate an
 * existing database of users from one password type to another.
 */
class FallbackPasswordHasher extends AbstractPasswordHasher
{
    /**
     * Holds the list of password hasher objects that will be used
     *
     * @var \Authentication\PasswordHasher\PasswordHasherCollectionInterface
     */
    protected $hashers;

    /**
     * Constructor
     *
     * @param \Authentication\PasswordHasher\PasswordHasherCollectionInterface $hashers Hasher Collection
     */
    public function __construct(PasswordHasherCollectionInterface $hashers)
    {
        $this->hashers = $hashers;
    }

    /**
     * Adds a hasher
     *
     * @param PasswordHasherInterface $hasher Hasher instance.
     * @return void
     */
    public function addHasher(PasswordHasherInterface $hasher): void
    {
        $this->hashers->add($hasher);
    }

    /**
     * Generates password hash.
     *
     * Uses the first password hasher in the list to generate the hash
     *
     * @param string $password Plain text password to hash.
     * @return string Password hash
     */
    public function hash($password): string
    {
        return $this->hashers[0]->hash($password);
    }

    /**
     * Verifies that the provided password corresponds to its hashed version
     *
     * This will iterate over all configured hashers until one of them returns
     * true.
     *
     * @param string $password Plain text password to hash.
     * @param string $hashedPassword Existing hashed password.
     * @return bool True if hashes match else false.
     */
    public function check($password, string $hashedPassword): bool
    {
        /* @var $hasher \Authentication\PasswordHasher\PasswordHasherInterface */
        foreach ($this->hashers as $hasher) {
            if ($hasher->check($password, $hashedPassword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if the password need to be rehashed, with the first hasher present
     * in the list of hashers
     *
     * @param string $password The password to verify
     * @return bool
     */
    public function needsRehash(string $password): bool
    {
        return $this->hashers[0]->needsRehash($password);
    }
}

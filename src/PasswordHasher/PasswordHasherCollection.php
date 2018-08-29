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

use ArrayIterator;

/**
 * Password hashing class that use weak hashing algorithms. This class is
 * intended only to be used with legacy databases where passwords have
 * not been migrated to a stronger algorithm yet.
 */
class PasswordHasherCollection implements PasswordHasherCollectionInterface
{
    /**
     * List of Hashers
     *
     * @var array
     */
    protected $hashers = [];

    /**
     * Constructor
     *
     * @param array An array of password hashers
     */
    public function __construct(array $hashers = [])
    {
        foreach ($hashers as $hasher) {
            $this->add($hasher);
        }
    }

    /**
     * Adds a password hasher to the collection
     *
     * @param \Authentication\PasswordHasher\PasswordHasherInterface $hasher Hasher
     * @return void
     */
    public function add(PasswordHasherInterface $hasher)
    {
        $this->hashers[] = $hasher;
    }

    /**
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->hashers);
    }
}

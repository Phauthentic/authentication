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
namespace Authentication\Authenticator;

use ArrayIterator;
use Traversable;

/**
 * Authenticator Collection
 */
class AuthenticatorCollection implements AuthenticatorCollectionInterface
{
    /**
     * List of authenticators
     *
     * @var array
     */
    protected $authenticators = [];

    /**
     * Constructor
     *
     * @param iterable $autheticators Authenticators
     */
    public function __construct(iterable $autheticators = [])
    {
        foreach ($autheticators as $autheticator) {
            $this->add($autheticator);
        }
    }

    /**
     * Returns true if a collection is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->authenticators);
    }

    /**
     * {@inheritDoc}
     */
    public function add(AuthenticatorInterface $authenticator): void
    {
        $this->authenticators[] = $authenticator;
    }

    /**
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->authenticators);
    }
}

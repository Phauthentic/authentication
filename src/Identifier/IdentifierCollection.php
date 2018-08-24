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

use ArrayIterator;

/**
 * Identifier Collection
 */
class IdentifierCollection implements IdentifierCollectionInterface
{

    /**
     * Identifier list
     *
     * @var array
     */
    protected $identifiers;

    /**
     * Constructor
     *
     * @param array $config Configuration
     */
    public function __construct(array $identifiers = [])
    {
        foreach ($identifiers as $identifier) {
            $this->add($identifier);
        }
    }

    /**
     * Adds an identifier to the collection
     *
     * @param \Authentication\Identifier\IdentifierInterface $identifier Identifier
     * @return void
     */
    public function add(IdentifierInterface $identifier)
    {
        $this->identifiers[] = $identifier;
    }

    /**
     * Returns true if a collection is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->identifiers);
    }

    /**
     * Returns iterator.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->identifiers);
    }
}

<?php 
declare(strict_types=1);
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
namespace Phauthentic\Authentication\Identity;

use ArrayAccess;
use BadMethodCallException;

/**
 * Identity object
 */
class Identity implements IdentityInterface
{
    /**
     * Default configuration.
     *
     * - `fieldMap` Mapping of fields
     *
     * @var array
     */
    protected $defaultConfig = [
        'fieldMap' => [
            'id' => 'id'
        ]
    ];

    /**
     * Config
     *
     * @var array
     */
    protected $config = [];

    /**
     * Identity data
     *
     * @var \ArrayAccess
     */
    protected $data;

    /**
     * Constructor
     *
     * @param \ArrayAccess $data Identity data
     * @param array $config Config options
     * @throws \InvalidArgumentException When invalid identity data is passed.
     */
    public function __construct(ArrayAccess $data, array $config = [])
    {
        $this->config = array_merge_recursive($this->defaultConfig, $config);
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->get('id');
    }

    /**
     * Get data from the identity using object access.
     *
     * @param string $field Field in the user data.
     * @return mixed
     */
    public function __get($field)
    {
        return $this->get($field);
    }

    /**
     * Check if the field isset() using object access.
     *
     * @param string $field Field in the user data.
     * @return mixed
     */
    public function __isset($field)
    {
        return $this->get($field) !== null;
    }

    /**
     * Get data from the identity
     *
     * @param string $field Field in the user data.
     * @return mixed
     */
    protected function get($field)
    {
        $map = $this->config['fieldMap'];
        if (isset($map[$field])) {
            $field = $map[$field];
        }

        if (isset($this->data[$field])) {
            return $this->data[$field];
        }

        return null;
    }

    /**
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset Offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->get($offset) !== null;
    }

    /**
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset Offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value Value
     * @throws \BadMethodCallException
     * @return mixed
     */
    public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException('Identity does not allow wrapped data to be mutated.');
    }

    /**
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset Offset
     * @throws \BadMethodCallException
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new BadMethodCallException('Identity does not allow wrapped data to be mutated.');
    }

    /**
     * {@inheritDoc}
     */
    public function getOriginalData(): ArrayAccess
    {
        return $this->data;
    }
}

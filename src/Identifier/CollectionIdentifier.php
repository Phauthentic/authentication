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

use ArrayAccess;
use InvalidArgumentException;
use RuntimeException;

/**
 * Callback Identifier
 */
class CollectionIdentifier extends AbstractIdentifier
{

    /**
     * Errors
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Identifier Collection
     *
     * @var \Authentication\Identifier\IdentifierCollectionInterface
     */
    protected $collection;

    /**
     * Constructor
     *
     * @param \Authentication\Identifier\IdentifierCollectionInterface
     * @param array $config Config options
     */
    public function __construct(IdentifierCollectionInterface $collection, array $config = []) {
        parent::__construct($config);
        $this->collection = $collection;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Identifies an user or service by the passed credentials
     *
     * @param array $credentials Authentication credentials
     * @return \ArrayAccess|array|null
     */
    public function identify(array $credentials)
    {
        /** @var \Authentication\Identifier\IdentifierInterface $identifier */
        foreach ($this->collection as $identifier) {
            $result = $identifier->identify($credentials);
            if ($result) {
                return $result;
            }
            $this->_errors[get_class($identifier)] = $identifier->getErrors();
        }

        return null;
    }
}

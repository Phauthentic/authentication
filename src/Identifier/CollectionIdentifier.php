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
namespace Authentication\Identifier;

use ArrayAccess;

/**
 * Callback Identifier
 */
class CollectionIdentifier implements IdentifierInterface
{
    /**
     * Errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Identifier Collection
     *
     * @var iterable
     */
    protected $collection;

    /**
     * Constructor
     *
     * @param iterable $collection Identifier collection.
     */
    public function __construct(iterable $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Identifies an user or service by the passed credentials
     *
     * @param array $credentials Authentication credentials
     * @return \ArrayAccess|null
     */
    public function identify(array $credentials): ?ArrayAccess
    {
        /** @var \Authentication\Identifier\IdentifierInterface $identifier */
        foreach ($this->collection as $identifier) {
            $result = $identifier->identify($credentials);
            if ($result) {
                return $result;
            }
            $this->errors[get_class($identifier)] = $identifier->getErrors();
        }

        return null;
    }
}

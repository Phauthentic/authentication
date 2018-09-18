<?php
declare(strict_types=1);
/**
 * Copyright (c) Phauthentic (https://github.com/Phauthentic)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Phauthentic (https://github.com/Phauthentic)
 * @link          https://github.com/Phauthentic
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Phauthentic\Authentication\Identifier\Resolver;

use ArrayAccess;

/**
 * A simple callable resolver that allows a quick implementation of any kind
 * of resolver logic
 */
class CallbackResolver implements ResolverInterface
{
    /**
     * Callable
     *
     * @var callable
     */
    protected $callable;

    /**
     * Constructor.
     *
     * @param callable $callable Callable.
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * {@inheritDoc}
     */
    public function find(array $conditions): ?ArrayAccess
    {
        $callable = $this->callable;

        return $callable($conditions);
    }
}

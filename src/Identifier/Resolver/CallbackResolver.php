<?php
declare(strict_types=1);

namespace Authentication\Identifier\Resolver;

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

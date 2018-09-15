<?php
declare(strict_types=1);

namespace Authentication\Identifier\Resolver;

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
     * @param array $config Config array.
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * {@inheritDoc}
     */
    public function find(array $conditions, $type = self::TYPE_AND)
    {
        $callable = $this->callable;

        return $callable($conditions, $type);
    }
}

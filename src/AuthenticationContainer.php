<?php
namespace Authentication;

use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * Authentication Service
 */
class AuthenticationContainer implements ContainerInterface {

    /**
     * Container Content
     *
     * @var array
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param string|null $configFile Config file
     */
    public function __construct(?string $configFile = null)
    {
        if (is_null($configFile)) {
            $ds = DIRECTORY_SEPARATOR;
            $configFile = base_path(__DIR__) . '..' . $ds . 'config' . $ds . 'container.php';
        }

        require_once $configFile;

        if (!isset($container) || !is_array($container)) {
            throw new RuntimeException('Failed to load container config');
        }

        $this->container = $container;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     * @return mixed Entry.
     */
    public function get($id)
    {
        if (!$this->has($id)) {

        }

        if (is_closure($this->container[$id])) {
            return $this->container[$id]($this);
        }

        return $this->container[$id];
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     * @return bool
     */
    public function has($id)
    {
        return isset($this->container[$id]);
    }

    /**
     *
     */
    public function set($id, $object)
    {
        $this->container[$id] = $object;
    }
}

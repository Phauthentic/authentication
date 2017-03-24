<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\Identifier;

use Authentication\AbstractCollection;
use Cake\Core\App;
use RuntimeException;

class IdentifierCollection extends AbstractCollection
{

    /**
     * Errors
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Identifies an user or service by the passed credentials
     *
     * @param mixed $credentials Authentication credentials
     * @return \Cake\Datasource\EntityInterface|null
     */
    public function identify($credentials)
    {
        foreach ($this->_loaded as $name => $identifier) {
            $result = $identifier->identify($credentials);
            if ($result) {
                return $result;
            }
            $this->_errors[$name] = $identifier->getErrors();
        }

        return null;
    }

    /**
     * Creates identifier instance.
     *
     * @param string $className Identifier class.
     * @param string $alias Identifier alias.
     * @param array $config Config array.
     * @return IdentifierInterface
     * @throws \RuntimeException
     */
    protected function _create($className, $alias, $config)
    {
        $identifier = new $className($config);
        if (!($identifier instanceof IdentifierInterface)) {
            throw new RuntimeException(sprintf(
                'Identifier class `%s` must implement \Auth\Authentication\IdentifierInterface',
                $className
            ));
        }

        return $identifier;
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
     * Resolves identifier class name.
     *
     * @param string $class Class name to be resolbed.
     * @return string
     */
    protected function _resolveClassName($class)
    {
        return App::className($class, 'Identifier', 'Identifier');
    }

    /**
     *
     * @param string $class Missing class.
     * @param string $plugin Class plugin.
     * @return void
     * @throws \RuntimeException
     */
    protected function _throwMissingClassError($class, $plugin)
    {
        $message = sprintf('Identifier class `%s` was not found.', $class);
        throw new RuntimeException($message);
    }
}

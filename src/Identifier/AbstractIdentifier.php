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

use Cake\Core\InstanceConfigTrait;

abstract class AbstractIdentifier implements IdentifierInterface
{
    /**
     * Default configuration
     *
     * @var array
     */
    protected $defaultConfig = [];

    /**
     * Config Options
     *
     * @var array
     */
    protected $config = [];

    /**
     * Errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Returns errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}

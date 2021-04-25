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
namespace Phauthentic\Authentication\Identifier;

use ArrayAccess;
use RuntimeException;

/**
 * Callback Identifier
 */
class CallbackIdentifier extends AbstractIdentifier
{

    /**
     * @var callable
     */
    protected $callable;

    /**
     * {@inheritDoc}
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * {@inheritDoc}
     */
    public function identify(array $data): ?ArrayAccess
    {
        $callback = $this->callable;

        return $callback($data);
    }
}

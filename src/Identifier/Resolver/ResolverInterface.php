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
namespace Phauthentic\Authentication\Identifier\Resolver;

use ArrayAccess;

interface ResolverInterface
{

    /**
     * Returns identity for given conditions.
     *
     * Should return `null` if the conditions cannot be resolved.
     *
     * @param array $conditions Find conditions.
     * @return \ArrayAccess|null
     */
    public function find(array $conditions): ?ArrayAccess;
}

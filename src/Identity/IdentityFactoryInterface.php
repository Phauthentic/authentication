<?php

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

declare(strict_types=1);

namespace Phauthentic\Authentication\Identity;

use ArrayAccess;

interface IdentityFactoryInterface
{
    /**
     * Creates identity object.
     *
     * @param \ArrayAccess $data Data.
     * @return \Phauthentic\Authentication\Identity\IdentityInterface
     */
    public function create(ArrayAccess $data): IdentityInterface;
}

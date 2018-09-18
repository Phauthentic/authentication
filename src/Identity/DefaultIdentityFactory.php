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
namespace Phauthentic\Authentication\Identity;

use ArrayAccess;

class DefaultIdentityFactory implements IdentityFactoryInterface
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * Constructor.
     *
     * @param array $config Config.
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function create(ArrayAccess $data): IdentityInterface
    {
        return new Identity($data, $this->config);
    }
}

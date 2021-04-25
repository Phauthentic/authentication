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

namespace Phauthentic\Authentication\Test\TestCase\Identifier\Resolver;

use ArrayObject;
use Phauthentic\Authentication\Identifier\Resolver\PdoResolver;
use Phauthentic\Authentication\Test\TestCase\AuthenticationTestCase as TestCase;

/**
 * PDO Resolver Test
 */
class PdoResolverTest extends TestCase
{
    /**
     * testFindWithSuccess
     *
     * @return void
     */
    public function testFindWithSuccess(): void
    {
        $pdo = static::getPDO();

        $resolver = new PdoResolver($pdo, 'SELECT * FROM users WHERE username = :username');
        $result = $resolver->find(['username' => 'florian']);

        $this->assertInstanceOf(ArrayObject::class, $result);
        $this->assertEquals($result['username'], 'florian');
    }
}

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
use Phauthentic\Authentication\Identifier\Resolver\PdoStatementResolver;
use Phauthentic\Authentication\Test\TestCase\AuthenticationTestCase as TestCase;

/**
 * PDO Statement esolver
 */
class PdoStatementResolverTest extends TestCase
{
    /**
     * testFindWithSuccess
     *
     * @return void
     */
    public function testFindWithSuccess(): void
    {
        $pdo = static::getPDO();
        $statement = $pdo->query('SELECT * FROM users WHERE username = :username');

        $resolver = new PdoStatementResolver($statement);
        $result = $resolver->find(['username' => 'florian']);

        $this->assertInstanceOf(ArrayObject::class, $result);
        $this->assertEquals($result['username'], 'florian');
    }

    /**
     * testFindWithNullResult
     *
     * @return void
     */
    public function testFindWithNullResult(): void
    {
        $pdo = static::getPDO();
        $statement = $pdo->query('SELECT * FROM users WHERE username = :username');

        $resolver = new PdoStatementResolver($statement);
        $result = $resolver->find(['username' => 'doesnotexist']);

        $this->assertNull($result);
    }

    /**
     * testFindWithNullResult
     *
     * @return void
     */
    public function testFindWithOtherQuery(): void
    {
        $pdo = static::getPDO();
        $statement = $pdo->query('SELECT * FROM users WHERE username = :username AND password = :password');

        $resolver = new PdoStatementResolver($statement);
        $result = $resolver->find(['username' => 'florian', 'password' => '$2y$10$INLSuN.AbQ1IB27NYH.Wi.NqrLGLRb9SBxn6wUOqUTIDHcNj6ZN2q']);

        $this->assertInstanceOf(ArrayObject::class, $result);
        $this->assertEquals($result['username'], 'florian');
    }
}

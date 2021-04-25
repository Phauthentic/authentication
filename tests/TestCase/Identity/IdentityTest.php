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

namespace Phauthentic\Authentication\Test\TestCase\Identity;

use ArrayObject;
use Phauthentic\Authentication\Identity\Identity;
use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class IdentityTest extends TestCase
{
    /**
     * Test getIdentifier()
     *
     * @return void
     */
    public function testGetIdentifier()
    {
        $data = new ArrayObject([
            'id' => 1,
            'username' => 'florian'
        ]);

        $identity = new Identity($data);

        $result = $identity->getIdentifier();
        $this->assertEquals(1, $result);

        $this->assertEquals('florian', $identity->username);
    }

    /**
     * Test mapping fields
     *
     * @return void
     */
    public function testFieldMapping()
    {
        $data = new ArrayObject([
            'id' => 1,
            'first_name' => 'florian',
            'mail' => 'info@cakephp.org'
        ]);

        $identity = new Identity($data, [
            'fieldMap' => [
                'username' => 'first_name',
                'email' => 'mail'
            ]
        ]);

        $this->assertTrue(isset($identity['username']), 'Renamed field responds to isset');
        $this->assertTrue(isset($identity['first_name']), 'old alias responds to isset.');
        $this->assertFalse(isset($identity['missing']));

        $this->assertTrue(isset($identity->username), 'Renamed field responds to isset');
        $this->assertTrue(isset($identity->first_name), 'old alias responds to isset.');
        $this->assertFalse(isset($identity->missing));

        $this->assertSame('florian', $identity['username'], 'renamed field responsds to offsetget');
        $this->assertSame('florian', $identity->username, 'renamed field responds to__get');
        $this->assertNull($identity->missing);
    }

    /**
     * Identities disallow data being unset.
     *
     * @return void
     */
    public function testOffsetUnsetError()
    {
        $this->expectException(BadMethodCallException::class);
        $data = new ArrayObject([
            'id' => 1,
        ]);
        $identity = new Identity($data);
        unset($identity['id']);

        $identity['username'] = 'mark';
    }

    /**
     * Identities disallow data being set.
     *
     * @return void
     */
    public function testOffsetSetError()
    {
        $this->expectException(BadMethodCallException::class);
        $data = new ArrayObject([
            'id' => 1,
        ]);
        $identity = new Identity($data);
        $identity['username'] = 'mark';
    }

    /**
     * Test array data.
     */
    public function testBuildArray()
    {
        $data = new ArrayObject(['username' => 'robert']);
        $identity = new Identity($data);
        $this->assertEquals($data['username'], $identity['username']);
    }

    /**
     * Test getOriginalData() method
     *
     * @return void
     */
    public function testGetOriginalData()
    {
        $data = new ArrayObject(['email' => 'info@cakephp.org']);

        $identity = new Identity($data);
        $this->assertSame($data, $identity->getOriginalData());
    }
}

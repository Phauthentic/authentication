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
namespace Authentication\Test\TestCase\Identifier;

use ArrayAccess;
use ArrayObject;
use Authentication\Identifier\CallbackIdentifier;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MyCallback
{

    public static function callme($data)
    {
        return new ArrayObject();
    }
}

class CallbackIdentifierTest extends TestCase
{
    /**
     * testIdentify
     *
     * @return void
     */
    public function testIdentify(): void
    {
        $callback = function ($data) {
            if (isset($data['username']) && $data['username'] === 'florian') {
                return new ArrayObject($data);
            }

            return null;
        };

        $identifier = new CallbackIdentifier($callback);

        $result = $identifier->identify([]);
        $this->assertNull($result);

        $result = $identifier->identify(['username' => 'larry']);
        $this->assertNull($result);

        $result = $identifier->identify(['username' => 'florian']);
        $this->assertInstanceOf(ArrayAccess::class, $result);
    }

    /**
     * testValidCallable
     *
     * @return void
     */
    public function testValidCallable(): void
    {
        $identifier = new CallbackIdentifier(function () {
            return new ArrayObject();
        });
        $result = $identifier->identify([]);

        $this->assertInstanceOf(ArrayAccess::class, $result);

        $identifier = new CallbackIdentifier([MyCallback::class, 'callme']);
        $result = $identifier->identify([]);

        $this->assertInstanceOf(ArrayAccess::class, $result);
    }

    /**
     * testInvalidCallbackTypeObject
     *
     * @expectedException RuntimeException
     */
    public function testInvalidReturnValue(): void
    {
        $identifier = new CallbackIdentifier(function ($data) {
            return 'no';
        });
        $identifier->identify([]);
    }
}

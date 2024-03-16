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

namespace Phauthentic\Authentication\Test\TestCase\Authenticator;

use ArrayObject;
use Phauthentic\Authentication\Authenticator\Result;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    /**
     * testConstructorEmptyData
     *
     * @return void
     */
    public function testConstructorEmptyData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Identity data can not be empty with status success.');

        new Result(null, Result::SUCCESS);
    }

    /**
     * testIsValid
     *
     * @return void
     */
    public function testIsValid(): void
    {
        $result = new Result(null, Result::FAILURE_CREDENTIALS_INVALID);
        $this->assertFalse($result->isValid());

        $result = new Result(null, Result::FAILURE_CREDENTIALS_MISSING);
        $this->assertFalse($result->isValid());

        $result = new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
        $this->assertFalse($result->isValid());

        $result = new Result(null, Result::FAILURE_OTHER);
        $this->assertFalse($result->isValid());

        $entity = new ArrayObject(['user' => 'florian']);
        $result = new Result($entity, Result::SUCCESS);
        $this->assertTrue($result->isValid());
    }

    /**
     * testGetIdentity
     *
     * @return void
     */
    public function testGetIdentity(): void
    {
        $entity = new ArrayObject(['user' => 'florian']);
        $result = new Result($entity, Result::SUCCESS);
        $this->assertEquals($entity, $result->getData());
    }

    /**
     * testGetCode
     *
     * @return void
     */
    public function testGetCode(): void
    {
        $result = new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getStatus());

        $entity = new ArrayObject(['user' => 'florian']);
        $result = new Result($entity, Result::SUCCESS);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
    }

    /**
     * testGetErrors
     *
     * @return void
     */
    public function testGetErrors(): void
    {
        $messages = [
            'Out of coffee!',
            'Out of beer!'
        ];
        $entity = new ArrayObject(['user' => 'florian']);
        $result = new Result($entity, Result::FAILURE_OTHER, $messages);
        $this->assertEquals($messages, $result->getErrors());
    }
}

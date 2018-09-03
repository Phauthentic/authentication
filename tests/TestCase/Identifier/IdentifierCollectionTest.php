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

use Authentication\Authenticator\AuthenticatorInterface;
use Authentication\Identifier\IdentifierCollection;
use Authentication\Identifier\IdentifierInterface;
use Authentication\Identifier\Resolver\OrmResolver;
use Authentication\Identity;
use Authentication\IdentityInterface;
use Authentication\Identifier\PasswordIdentifier;
use Authentication\Test\TestCase\AuthenticationTestCase as TestCase;
use TestApp\Authentication\Identifier\InvalidIdentifier;

class IdentifierCollectionTest extends TestCase
{
    /**
     * testConstruct
     *
     * @return void
     */
    public function testConstruct(): void
    {
        $collection = new IdentifierCollection();
        $this->assertTrue($collection->isEmpty());

        $collection = new IdentifierCollection([
            $this->getMockBuilder(IdentifierInterface::class)->getMock()
        ]);
        $this->assertFalse($collection->isEmpty());
    }

    /**
     * testSet
     *
     * @return void
     */
    public function testAdd(): void
    {
        $mockIdentifier = $this->createMock(IdentifierInterface::class);
        $collection = new IdentifierCollection();
        $collection->add($mockIdentifier);
        foreach ($collection as $identifier) {
            $this->assertSame($mockIdentifier, $identifier);
        }
    }

    /**
     * testIsEmpty
     *
     * @return void
     */
    public function testIsEmpty(): void
    {
        $collection = new IdentifierCollection();
        $this->assertTrue($collection->isEmpty());

        $mock = $this->createMock(IdentifierInterface::class);
        $collection->add($mock);
        $this->assertFalse($collection->isEmpty());
    }

    /**
     * testIterator
     *
     * @return void
     */
    public function testIterator(): void
    {
        $identifier = $this->createMock(IdentifierInterface::class);
        $collection = new IdentifierCollection();
        $collection->add($identifier);

        $this->assertContains($identifier, $collection);
    }
}

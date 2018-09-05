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
namespace Authentication\Test\TestCase\Authenticator;

use Authentication\Authenticator\AuthenticatorCollection;
use Authentication\Authenticator\AuthenticatorInterface;
use Authentication\Authenticator\FormAuthenticator;
use Authentication\Identifier\IdentifierCollection;
use Cake\TestSuite\TestCase;
use TestApp\Authentication\Identifier\InvalidIdentifier;

class AuthenticatorCollectionTest extends TestCase
{

    /**
     * Test constructor.
     *
     * @return void
     */
    public function testConstruct()
    {
        $authenticator = $this->createMock(AuthenticatorInterface::class);
        $collection = new AuthenticatorCollection([$authenticator]);

        $this->assertFalse($collection->isEmpty());
    }

    /**
     * testSet
     *
     * @return void
     */
    public function testAdd()
    {
        $authenticator = $this->createMock(AuthenticatorInterface::class);

        $collection = new AuthenticatorCollection();
        $collection->add($authenticator);

        $this->assertFalse($collection->isEmpty());
    }

    /**
     * testIsEmpty
     *
     * @return void
     */
    public function testIsEmpty()
    {
        $identifiers = $this->createMock(IdentifierCollection::class);
        $collection = new AuthenticatorCollection();
        $this->assertTrue($collection->isEmpty());

        $collection->add($this->createMock(AuthenticatorInterface::class));
        $this->assertFalse($collection->isEmpty());
    }

    /**
     * testIterator
     *
     * @return void
     */
    public function testIterator()
    {
        $authenticator = $this->createMock(AuthenticatorInterface::class);

        $collection = new AuthenticatorCollection();
        $collection->add($authenticator);

        $this->assertContains($authenticator, $collection);
    }
}

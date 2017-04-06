<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\Test\TestCase\Identifier;

use ArrayObject;
use Authentication\Identifier\PasswordIdentifier;
use Authentication\Identifier\Resolver\ResolverInterface;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Authentication\PasswordHasher\PasswordHasherInterface;
use Authentication\PasswordHasher\WeakPasswordHasher;
use Authentication\Test\TestCase\AuthenticationTestCase as TestCase;

class PasswordIdentifierTest extends TestCase
{

    /**
     * testIdentifyValid
     *
     * @return void
     */
    public function testIdentifyValid()
    {
        $resolver = $this->createMock(ResolverInterface::class);
        $hasher = $this->createMock(PasswordHasherInterface::class);

        $user = new ArrayObject([
            'username' => 'mariano',
            'password' => 'h45hedpa55w0rd'
        ]);

        $resolver->expects($this->once())
            ->method('find')
            ->with(['username' => 'mariano'])
            ->willReturn($user);

        $hasher->expects($this->once())
            ->method('check')
            ->with('password', 'h45hedpa55w0rd')
            ->willReturn(true);

        $identifier = new PasswordIdentifier();
        $identifier->setResolver($resolver)->setPasswordHasher($hasher);

        $result = $identifier->identify([
            'username' => 'mariano',
            'password' => 'password'
        ]);

        $this->assertInstanceOf('\ArrayAccess', $result);
        $this->assertSame($user, $result);
        $this->assertArrayNotHasKey('password', $result);
    }

    public function testIdentifyNeedsRehash()
    {
        $resolver = $this->createMock(ResolverInterface::class);
        $hasher = $this->createMock(PasswordHasherInterface::class);

        $user = new ArrayObject([
            'username' => 'mariano',
            'password' => 'h45hedpa55w0rd'
        ]);

        $resolver->method('find')
            ->willReturn($user);

        $hasher->method('check')
            ->willReturn(true);

        $hasher->expects($this->once())
            ->method('needsRehash')
            ->with('h45hedpa55w0rd')
            ->willReturn(true);

        $identifier = new PasswordIdentifier();
        $identifier->setResolver($resolver)->setPasswordHasher($hasher);

        $result = $identifier->identify([
            'username' => 'mariano',
            'password' => 'password'
        ]);

        $this->assertInstanceOf('\ArrayAccess', $result);
        $this->assertTrue($identifier->needsPasswordRehash());
    }

    /**
     * testIdentifyInvalid
     *
     * @return void
     */
    public function testIdentifyInvalidUser()
    {
        $resolver = $this->createMock(ResolverInterface::class);
        $hasher = $this->createMock(PasswordHasherInterface::class);

        $resolver->expects($this->once())
            ->method('find')
            ->with(['username' => 'does-not'])
            ->willReturn(null);

        $hasher->expects($this->never())
            ->method('check');

        $identifier = new PasswordIdentifier();
        $identifier->setResolver($resolver)->setPasswordHasher($hasher);

        $result = $identifier->identify([
            'username' => 'does-not',
            'password' => 'exist'
        ]);

        $this->assertNull($result);
    }

    /**
     * testIdentifyInvalid
     *
     * @return void
     */
    public function testIdentifyInvalidPassword()
    {
        $resolver = $this->createMock(ResolverInterface::class);
        $hasher = $this->createMock(PasswordHasherInterface::class);

        $user = new ArrayObject([
            'username' => 'mariano',
            'password' => 'h45hedpa55w0rd'
        ]);

        $resolver->expects($this->once())
            ->method('find')
            ->with(['username' => 'mariano'])
            ->willReturn($user);

        $hasher->expects($this->once())
            ->method('check')
            ->with('wrongpassword', 'h45hedpa55w0rd')
            ->willReturn(false);

        $identifier = new PasswordIdentifier();
        $identifier->setResolver($resolver)->setPasswordHasher($hasher);

        $result = $identifier->identify([
            'username' => 'mariano',
            'password' => 'wrongpassword'
        ]);

        $this->assertNull($result);
    }

    /**
     * testIdentifyValid
     *
     * @return void
     */
    public function testIdentifyMultiField()
    {
        $resolver = $this->createMock(ResolverInterface::class);
        $hasher = $this->createMock(PasswordHasherInterface::class);

        $user = new ArrayObject([
            'username' => 'mariano',
            'email' => 'mariano@example.com',
            'password' => 'h45hedpa55w0rd'
        ]);

        $resolver->expects($this->once())
            ->method('find')
            ->with([
                'username' => 'mariano@example.com',
                'email' => 'mariano@example.com'
            ], 'OR')
            ->willReturn($user);

        $hasher->expects($this->once())
            ->method('check')
            ->with('password', 'h45hedpa55w0rd')
            ->willReturn(true);

        $hasher->expects($this->once())
            ->method('needsRehash')
            ->with('h45hedpa55w0rd');

        $identifier = new PasswordIdentifier([
            'fields' => ['username' => ['email', 'username']]
        ]);
        $identifier->setResolver($resolver)->setPasswordHasher($hasher);

        $result = $identifier->identify([
            'username' => 'mariano@example.com',
            'password' => 'password'
        ]);

        $this->assertInstanceOf('\ArrayAccess', $result);
        $this->assertSame($user, $result);
        $this->assertArrayNotHasKey('password', $result);
    }

    /**
     * testDefaultPasswordHasher
     *
     * @return void
     */
    public function testDefaultPasswordHasher()
    {
        $identifier = new PasswordIdentifier();
        $hasher = $identifier->getPasswordHasher();
        $this->assertInstanceOf(DefaultPasswordHasher::class, $hasher);
    }

    /**
     * testCustomPasswordHasher
     *
     * @return void
     */
    public function testCustomPasswordHasher()
    {
        $identifier = new PasswordIdentifier([
            'passwordHasher' => 'Authentication.Weak'
        ]);
        $hasher = $identifier->getPasswordHasher();
        $this->assertInstanceOf(WeakPasswordHasher::class, $hasher);
    }
}

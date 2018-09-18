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
use Authentication\Identifier\Ldap\AdapterInterface;
use Authentication\Identifier\Ldap\ExtensionAdapter;
use Authentication\Identifier\LdapIdentifier;
use ErrorException;
use PHPUnit\Framework\TestCase;

class LdapIdentifierTest extends TestCase
{

    /**
     * testIdentify
     *
     * @return void
     */
    public function testIdentify()
    {
        $host = 'ldap.example.com';
        $bind = function ($username) {
            return 'cn=' . $username . ',dc=example,dc=com';
        };
        $options = [
            'foo' => 3
        ];

        $ldap = $this->createMock(AdapterInterface::class);

        $identifier = (new LdapIdentifier($ldap, $host, $bind))
            ->setLdapOptions($options);

        $ldap->expects($this->once())
            ->method('connect')
            ->with($host, 389, $options);
        $ldap->expects($this->once())
            ->method('bind')
            ->with('cn=john,dc=example,dc=com', 'doe')
            ->willReturn(true);

        $result = $identifier->identify([
            'username' => 'john',
            'password' => 'doe'
        ]);

        $this->assertInstanceOf(ArrayAccess::class, $result);
    }

    /**
     * testIdentifyMissingCredentials
     *
     * @return void
     */
    public function testIdentifyMissingCredentials()
    {
        $ldap = $this->createMock(AdapterInterface::class);
        $ldap->method('bind')
            ->willReturn(false);

        $host = 'ldap.example.com';
        $bind = function () {
            return 'dc=example,dc=com';
        };
        $identifier = new LdapIdentifier($ldap, $host, $bind);

        $result = $identifier->identify([
            'username' => 'john',
            'password' => 'doe'
        ]);
        $this->assertNull($result);

        $resultTwo = $identifier->identify([]);
        $this->assertNull($resultTwo);
    }

    /**
     * testLdapExtensionAdapter
     *
     * @return void
     */
    public function testLdapExtensionAdapter()
    {
        if (!extension_loaded('ldap')) {
            $this->markTestSkipped('LDAP extension is not loaded.');
        }
        $identifier = new LdapIdentifier(
            new ExtensionAdapter(),
            'ldap.example.com',
            function () {
                return 'dc=example,dc=com';
            }
        );

        $this->assertInstanceOf(ExtensionAdapter::class, $identifier->getAdapter());
    }

    /**
     * testHandleError
     *
     * @return void
     */
    public function testHandleError()
    {
        $ldap = $this->createMock(AdapterInterface::class);
        $ldap->method('bind')
            ->will($this->throwException(new ErrorException('This is an error.')));
        $ldap->method('getDiagnosticMessage')
            ->willReturn('This is another error.');

        $host = 'ldap.example.com';
        $bind = function () {
            return 'dc=example,dc=com';
        };
        $identifier = new LdapIdentifier($ldap, $host, $bind);

        $result = $identifier->identify([
            'username' => 'john',
            'password' => 'doe'
        ]);

        $this->assertSame($identifier->getErrors(), [
            'This is another error.',
            'This is an error.'
        ]);
    }
}

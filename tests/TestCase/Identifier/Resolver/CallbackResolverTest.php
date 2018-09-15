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
namespace Authentication\Test\TestCase\Identifier\Resolver;

use Authentication\Identifier\Resolver\CallbackResolver;
use Authentication\Identifier\Resolver\OrmResolver;
use Authentication\Test\TestCase\AuthenticationTestCase;
use Cake\Datasource\EntityInterface;

/**
 * CallbackResolverTest
 */
class CallbackResolverTest extends AuthenticationTestCase
{
    /**
     * testFindDefault
     *
     * @return void
     */
    public function testFindDefault(): void
    {
        $function = function($data) {
            if (isset($data['username']) && $data['username'] === 'mariano') {
                return new \ArrayObject([
                    'id' => 1,
                    'username' => 'mariano'
                ]);
            }
        };

        $resolver = new CallbackResolver($function);

        $user = $resolver->find([
            'username' => 'mariano'
        ]);

        $this->assertInstanceOf(\ArrayObject::class, $user);
        $this->assertEquals('mariano', $user['username']);
        $this->assertEquals(1, $user['id']);
    }
}

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
namespace Phauthentic\Authentication\Test\TestCase\Middleware;

use Phauthentic\Authentication\AuthenticationServiceProviderInterface;
use Phauthentic\Authentication\HttpFactory\ZendDiactoresResponseFactory;
use Phauthentic\Authentication\HttpFactory\ZendStreamFactory;
use Phauthentic\Authentication\Middleware\AuthenticationMiddleware;
use PHPUnit\Framework\TestCase;

/**
 * Authentication Middleware Test
 */
class AuthenticationMiddlewareTest extends TestCase
{
    use HttpEnvMockTrait;

    /**
     * testProcess
     *
     * @return void
     */
    public function testProcess(): void
    {
        $service = $this
            ->getMockBuilder(AuthenticationServiceProviderInterface::class)
            ->getMock();

        $middleware = new AuthenticationMiddleware(
            $service
        );
    }
}

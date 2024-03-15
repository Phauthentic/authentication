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

namespace Phauthentic\Authentication\Test\TestCase;

use ArrayObject;
use Nyholm\Psr7\Factory\Psr17Factory;
use Phauthentic\Authentication\Test\Fixture\FixtureInterface;
use Phauthentic\Authentication\Test\Fixture\UsersFixture;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * @author Robert Pustułka <robert.pustulka@gmail.com>
 */
class AuthenticationTestCase extends FixturizedTestCase
{
    /**
     * Returns users fixture.
     *
     * @return FixtureInterface
     */
    protected function createFixture(): FixtureInterface
    {
        return new UsersFixture();
    }

    public function getMockResponse()
    {
        return $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
    }

    public function getMockRequest(array $options = [])
    {
        $mockUri = $this
            ->getMockBuilder(UriInterface::class)
            ->getMock();

        if (isset($options['path'])) {
            $mockUri
                ->expects($this->any())
                ->method('getPath')
                ->willReturn($options['path']);
        }

        if (isset($options['host'])) {
            $mockUri
                ->expects($this->any())
                ->method('getHost')
                ->willReturn($options['host']);
        }

        $mockRequest = $this
            ->getMockBuilder(ServerRequestInterface::class)
            ->getMock();

        $mockRequest->expects($this->any())
            ->method('getUri')
            ->willReturn($mockUri);

        if (isset($options['parsedBody'])) {
            $mockRequest->expects($this->any())
                ->method('getParsedBody')
                ->willReturn($options['parsedBody']);
        }

        return $mockRequest;
    }

    public function getIdentity()
    {
        return new ArrayObject([
            'username' => 'robert',
            'password' => '$2y$10$VFTg46xeZ8/hU4zI.dtZVOfuz4AeIKAgZaB.uraGfcljXzid/xERa'
        ]);
    }
}

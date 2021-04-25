<?php

/**
 * Copyright (c) Phauthentic (https://github.com/Phauthentic)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Phauthentic (https://github.com/Phauthentic)
 * @link          https://github.com/Phauthentic
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Phauthentic\Authentication\Test\TestCase\UrlChecker;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * RequestMockTrait
 */
trait RequestMockTrait
{
    /**
     * Gets a mocked request that contains the URI
     *
     * @param $uriString URI String
     * @return mixed
     */
    public function getMockRequest($uriString)
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();

        $uri = $this->getMockBuilder(UriInterface::class)
            ->getMock();

        $uri->expects($this->any())
            ->method('getPath')
            ->willReturn($uriString);

        $uri->expects($this->any())
            ->method('__toString')
            ->willReturn('http://localhost' . $uriString);

        $request->expects($this->any())
            ->method('getUri')
            ->willReturn($uri);

        return $request;
    }
}

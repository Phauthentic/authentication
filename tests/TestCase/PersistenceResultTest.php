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

use Phauthentic\Authentication\PersistenceResult;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Robert Pustułka <robert.pustulka@gmail.com>
 */
class PersistenceResultTest extends AuthenticationTestCase
{
    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->request = $this
            ->getMockBuilder(ServerRequestInterface::class)
            ->getMock();

        $this->response = $this
            ->getMockBuilder(ResponseInterface::class)
            ->getMock();
    }

    /**
     * testGetResponse
     *
     * @return void
     */
    public function testGetResponse(): void
    {
        $result = new PersistenceResult(
            $this->request,
            $this->response
        );

        $this->assertInstanceOf(ResponseInterface::class, $result->getResponse());
    }

    /**
     * testGetRequest
     *
     * @return void
     */
    public function testGetRequest(): void
    {
        $result = new PersistenceResult(
            $this->request,
            $this->response
        );

        $this->assertInstanceOf(ServerRequestInterface::class, $result->getRequest());
    }
}

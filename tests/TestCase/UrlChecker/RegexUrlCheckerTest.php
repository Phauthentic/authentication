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
namespace Phauthentic\Authentication\Test\TestCase\UrlChecker;

use Phauthentic\Authentication\UrlChecker\RegexUrlChecker;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequestFactory;

/**
 * RegexUrlCheckerTest
 */
class RegexUrlCheckerTest extends TestCase
{

    /**
     * testCheckFailure
     *
     * @return void
     */
    public function testCheckFailure()
    {
        $checker = new RegexUrlChecker();

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/users/does-not-match']
        );

        $result = $checker->check($request, '%^/[a-z]{2}/users/login/?$%');
        $this->assertFalse($result);
    }

    /**
     * testCheckArray
     *
     * @return void
     */
    public function testCheck()
    {
        $checker = new RegexUrlChecker();
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/en/users/login']
        );

        $result = $checker->check($request, '%^/[a-z]{2}/users/login/?$%');
        $this->assertTrue($result);
    }

    /**
     * testCheckArray
     *
     * @return void
     */
    public function testCheckFull()
    {
        $checker = new RegexUrlChecker();
        $checker->checkFullUrl(true);
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/en/users/login']
        );

        $result = $checker->check($request, '%^http.*/[a-z]{2}/users/login/?$%');
        $this->assertTrue($result);
    }

    /**
     * testCheckArray
     *
     * @return void
     */
    public function testCheckFullFailure()
    {
        $checker = new RegexUrlChecker();
        $checker->checkFullUrl(true);
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/en/users/login']
        );

        $result = $checker->check($request, '%^https.*/[a-z]{2}/users/login/?$%');
        $this->assertFalse($result);
    }
}

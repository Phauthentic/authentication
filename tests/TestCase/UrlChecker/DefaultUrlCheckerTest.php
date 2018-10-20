<?php
declare(strict_types=1);
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

use Phauthentic\Authentication\UrlChecker\DefaultUrlChecker;
use PHPUnit\Framework\TestCase;

/**
 * DefaultUrlCheckerTest
 */
class DefaultUrlCheckerTest extends TestCase
{
    use RequestMockTrait;

    /**
     * testCheckFailure
     *
     * @return void
     */
    public function testCheckFailure()
    {
        $checker = new DefaultUrlChecker();

        $request = $this->getMockRequest('/users/does-not-match');
        $result = $checker->check($request, '/users/login');
        $this->assertFalse($result);
    }

    /**
     * testCheck
     *
     * @return void
     */
    public function testCheck()
    {
        $checker = new DefaultUrlChecker();

        $request = $this->getMockRequest('/users/login');
        $result = $checker->check($request, '/users/login');
        $this->assertTrue($result);
    }

    /**
     * testCheckFull
     *
     * @return void
     */
    public function testCheckFull()
    {
        $checker = new DefaultUrlChecker();
        $checker->checkFullUrl(true);

        $request = $this->getMockRequest('/users/login');
        $result = $checker->check($request, 'http://localhost/users/login');
        $this->assertTrue($result);
    }

    /**
     * testCheckFullFailure
     *
     * @return void
     */
    public function testCheckFullFailure()
    {
        $checker = new DefaultUrlChecker();
        $checker->checkFullUrl(true);

        $request = $this->getMockRequest('/users/does-not-match');
        $result = $checker->check($request, 'http://localhost/users/login');
        $this->assertFalse($result);
    }
}

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
namespace Phauthentic\Authentication\Test\TestCase;

use Phauthentic\Authentication\Test\Fixture\FixtureInterface;
use PDO;
use PHPUnit\DbUnit\Database\DefaultConnection;
use PHPUnit\DbUnit\DataSet\IDataSet;
use PHPUnit\DbUnit\TestCaseTrait;
use PHPUnit\Framework\TestCase;

abstract class FixturizedTestCase extends TestCase
{
    use TestCaseTrait;

    /**
     * @var PDO
     */
    static private $pdo = null;

    /**
     * @var DefaultConnection
     */
    private $connection = null;

    /**
     * Returns PDO instance.
     *
     * @return PDO
     */
    private static function getPDO(): PDO
    {
        if (self::$pdo == null) {
            self::$pdo = new PDO(getenv('PDO_DB_DSN'));
        }

        return self::$pdo;
    }

    /**
     * {@imheritDoc}
     */
    final public function getConnection(): DefaultConnection
    {
        if ($this->connection === null) {
            $this->connection = $this->createDefaultDBConnection(self::getPDO());
        }

        return $this->connection;
    }

    /**
     * {@imheritDoc}
     */
    protected function getDataSet(): IDataSet
    {
        $fixture = $this->createFixture();
        $fixture->createSchema(self::getPDO());

        return $fixture->getDataSet();
    }

    /**
     * This method should create a fixture fot this test.
     *
     * @return FixtureInterface
     */
    abstract protected function createFixture(): FixtureInterface;
}

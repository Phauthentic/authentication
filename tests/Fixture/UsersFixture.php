<?php
declare(strict_types=1);

namespace Authentication\Test\Fixture;

use Authentication\Test\Schema\UsersSchema;
use PDO;
use PHPUnit\DbUnit\DataSet\IDataSet;
use PHPUnit\DbUnit\DataSet\YamlDataSet;

class UsersFixture implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function createSchema(PDO $pdo): void
    {
        UsersSchema::create($pdo);
    }

    /**
     * Returns a path to the file with data set.
     *
     * @param string $name Filename.
     * @return string
     */
    protected function getFile(string $name): string
    {
        return dirname(dirname(__FILE__)) . '/data_set/' . $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getDataSet(): IDataSet
    {
        $yamlFile = $this->getFile('users.yml');

        return new YamlDataSet($yamlFile);
    }
}

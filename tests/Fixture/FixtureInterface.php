<?php

namespace Phauthentic\Authentication\Test\Fixture;

use PDO;
use PHPUnit\DbUnit\DataSet\IDataSet;

interface FixtureInterface
{
    /**
     * This method is used for initializing tables for this fixture.
     *
     * @param PDO $pdo PDO instance.
     * @return void
     */
    public function createSchema(PDO $pdo): void;

    /**
     * Returns IDataSet instance for this fixture..
     *
     * @return IDataSet
     */
    public function getDataSet(): IDataSet;
}

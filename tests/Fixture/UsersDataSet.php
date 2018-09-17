<?php
declare(strict_types=1);

namespace Authentication\Test\Fixture;

use PHPUnit\DbUnit\Database\Table;
use PHPUnit\DbUnit\DataSet\AbstractDataSet;
use PHPUnit\DbUnit\DataSet\DefaultTableMetaData;
use PHPUnit\DbUnit\DataSet\DefaultTable;
use PHPUnit\DbUnit\DataSet\DefaultTableIterator;
use PHPUnit\DbUnit\DataSet\ITableIterator;

class UsersDataSet extends AbstractDataSet
{
    /**
     * @var array
     */
    protected $tables = [
        'users'
    ];

    /**
     * @return array
     */
    public function getData(): array {
        $data = [
            ['username' => 'mariano', 'password' => '$2a$10$u05j8FjsvLBNdfhBhc21LOuVMpzpabVXQ9OpC2wO3pSO0q6t7HHMO', 'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'],
            ['username' => 'nate', 'password' => '$2a$10$u05j8FjsvLBNdfhBhc21LOuVMpzpabVXQ9OpC2wO3pSO0q6t7HHMO', 'created' => '2008-03-17 01:18:23', 'updated' => '2008-03-17 01:20:31'],
            ['username' => 'larry', 'password' => '$2a$10$u05j8FjsvLBNdfhBhc21LOuVMpzpabVXQ9OpC2wO3pSO0q6t7HHMO', 'created' => '2010-05-10 01:20:23', 'updated' => '2010-05-10 01:22:31'],
            ['username' => 'garrett', 'password' => '$2a$10$u05j8FjsvLBNdfhBhc21LOuVMpzpabVXQ9OpC2wO3pSO0q6t7HHMO', 'created' => '2012-06-10 01:22:23', 'updated' => '2012-06-12 01:24:31']
        ];

        foreach ($data as &$user) {
            $user['password'] = password_hash('password', PASSWORD_DEFAULT);
        }

        return $data;
    }

    /**
     * @param array $data Data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $tableName => $rows) {
            $columns = [];
            if (isset($rows[0])) {
                $columns = array_keys($rows[0]);
            }

            $metaData = new DefaultTableMetaData($tableName, $columns);
            $table = new DefaultTable($metaData);

            foreach ($rows as $row) {
                $table->addRow($row);
            }
            $this->tables[$tableName] = $table;
        }
    }

    /**
     * @return \PHPUnit\DbUnit\DataSet\ITableIterator
     */
    protected function createIterator($reverse = false): ITableIterator
    {
        return new DefaultTableIterator($this->tables, $reverse);
    }

    /**
     *
     */
    public function getTable($tableName): Table
    {
        if (!isset($this->tables[$tableName])) {
            throw new InvalidArgumentException("$tableName is not a table in the current database.");
        }

        return $this->tables[$tableName];
    }
}

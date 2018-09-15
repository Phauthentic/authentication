<?php
declare(strict_types=1);

namespace Authentication\Test\Fixture;

use PHPUnit\DbUnit\DataSet\AbstractDataSet;
use PHPUnit\DbUnit\DataSet\DefaultTableMetaData;
use PHPUnit\DbUnit\DataSet\DefaultTable;
use PHPUnit\DbUnit\DataSet\DefaultTableIterator;

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
        return [
            'users' => [
                'id' => 1,
                'username' => 'florian',
                'password' => 'password'
            ], [
                'id' => 2,
                'username' => 'robert',
                'password' => 'password'
            ]
        ];
    }

    /**
     * @param array $data
     */
    public function __construct(array $data)
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

    protected function createIterator($reverse = false)
    {
        return new DefaultTableIterator($this->tables, $reverse);
    }

    public function getTable($tableName)
    {
        if (!isset($this->tables[$tableName])) {
            throw new InvalidArgumentException("$tableName is not a table in the current database.");
        }

        return $this->tables[$tableName];
    }
}

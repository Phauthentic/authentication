<?php
declare(strict_types=1);

namespace Phauthentic\Authentication\Test\Schema;

use PDO;

class UsersSchema implements SchemaInterface
{
    /**
     * @var string
     */
    static private $sql =
        "CREATE TABLE IF NOT EXISTS users (
            id INT(11),
            username VARCHAR(128),
            password VARCHAR(128),
            created TIMESTAMP,
            updated TIMESTAMP,
            PRIMARY KEY (id)
        );";

    /**
     * {@inheritDoc}
     */
    public static function create(PDO $pdo): void
    {
        $pdo->query(static::$sql);
    }
}

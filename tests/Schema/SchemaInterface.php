<?php

namespace Phauthentic\Authentication\Test\Schema;

use PDO;

interface SchemaInterface
{
    /**
     * Creates a schema on PDO connection.
     *
     * @param PDO $pdo PDO
     * @return void
     */
    public static function create(PDO $pdo): void;
}

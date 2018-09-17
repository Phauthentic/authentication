<?php
declare(strict_types=1);

namespace Authentication\Test\Schema;

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

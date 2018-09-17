<?php
declare(strict_types=1);

namespace Authentication\Test\Fixture;

class CreateUsersTableSql {

    public static function getSchema() {
        return "CREATE TABLE users (
            id INT(11),
            username VARCHAR(128),
            password VARCHAR(128),
            token VARCHAR(128),
            PRIMARY KEY (id)
        );";
    }
}

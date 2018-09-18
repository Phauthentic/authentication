<?php
if (!getenv('PDO_DB_DSN')) {
    putenv('PDO_DB_DSN=sqlite::memory:');
}

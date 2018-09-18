<?php
namespace Authentication\Test\Resolver;

use ArrayAccess;
use ArrayObject;
use Authentication\Identifier\Resolver\ResolverInterface;
use PDO;

class TestResolver implements ResolverInterface
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function find(array $conditions): ?ArrayAccess
    {
        $where = [];
        foreach ($conditions as $field => $value) {
            $where[] = "$field = '$value'";
        }

        $sql = 'SELECT * FROM users WHERE ' . implode(' AND ', $where);

        $result = $this->pdo->query($sql)->fetch();

        if (!$result) {
            return null;
        }

        return new ArrayObject($result);
    }
}

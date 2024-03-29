<?php

/**
 * Copyright (c) Phauthentic (https://github.com/Phauthentic)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Phauthentic (https://github.com/Phauthentic)
 * @link          https://github.com/Phauthentic
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

declare(strict_types=1);

namespace Phauthentic\Authentication\Identifier\Resolver;

use ArrayAccess;
use ArrayObject;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * PDO Resolver
 */
class PdoResolver implements ResolverInterface
{
    /**
     * @var \PDO
     */
    protected PDO $pdo;

    /**
     * @var string
     */
    protected string $sql;

    /**
     * Constructor.
     *
     * @param \PDO $pdo PDO Instance
     * @param string $sql SQL String
     */
    public function __construct(PDO $pdo, string $sql)
    {
        $this->pdo = $pdo;
        $this->sql = $sql;
    }

    /**
     * Builds the statement
     *
     * @return \PDOStatement
     */
    protected function buildStatement(): PDOStatement
    {
        $statement = $this->pdo->prepare($this->sql);

        $error = $this->pdo->errorInfo();
        if ($error[0] !== '00000') {
            throw new PDOException($error[2], (int)$error[0]);
        }

        if (!$statement instanceof PDOStatement) {
            throw new RuntimeException(sprintf(
                'There was an error running your PDO resolver using this query: %s',
                $this->sql
            ));
        }

        return $statement;
    }

    /**
     * {@inheritDoc}
     */
    public function find(array $conditions): ?ArrayAccess
    {
        foreach ($conditions as $key => $value) {
            unset($conditions[$key]);
            $conditions[':' . $key] = $value;
        }

        $statement = $this->buildStatement();
        $statement->execute($conditions);
        $result = $statement->fetchAll();

        if (empty($result)) {
            return null;
        }

        return new ArrayObject($result[0]);
    }
}

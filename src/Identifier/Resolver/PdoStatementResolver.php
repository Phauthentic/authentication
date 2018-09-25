<?php
declare(strict_types=1);
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
namespace Phauthentic\Authentication\Identifier\Resolver;

use ArrayAccess;
use ArrayObject;
use PDO;
use PDOStatement;

/**
 * A simple php PDO Statement Resolver
 *
 * This should work with any system that is using PDO as a base an provides
 * access to the PDO object.
 */
class PdoStatementResolver implements ResolverInterface
{
    /**
     * Prepared statement
     *
     * @var \PDOStatement
     */
    protected $statement;

    /**
     * Constructor.
     *
     * @param \PDOStatement $statement A prepared statement to query the DB
     */
    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
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

        $this->statement->execute($conditions);
        $result = $this->statement->fetchAll();

        if (empty($result)) {
            return null;
        }

        return new ArrayObject($result[0]);
    }
}

<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Phpactor\Storage;

use Doctrine\DBAL\Connection;

class ConnectionWrapper
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getConnection()
    {
        $params = $this->connection->getParams();

        if (!isset($params['path'])) {
            throw new \InvalidArgumentException(sprintf(
                'No path set for sqlite database'
            ));
        }

        if (!file_exists($params['path'])) {
            $this->initializeSchema();
        }

        return $this->connection;
    }

    public function initializeSchema()
    {
        $schema = new Schema();
        $statements = $schema->toSql($this->connection->getDriver()->getDatabasePlatform());

        foreach ($statements as $statement) {
            $this->connection->exec($statement);
        }
    }
}

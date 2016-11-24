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

use Doctrine\DBAL\Schema\Schema as BaseSchema;

class Schema extends BaseSchema
{
    private $classTable;
    private $methodTable;
    private $paramTable;

    public function __construct()
    {
        parent::__construct();
        $this->createClass();
        $this->createMethod();
        $this->createParam();
    }

    private function createClass()
    {
        $table = $this->createTable('classes');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('namespace', 'string');
        $table->addColumn('name', 'string', ['notnull' => false]);
        $table->addColumn('file', 'datetime');
        $table->addColumn('doc', 'text');
        $table->setPrimaryKey(['id']);
        $this->classTable = $table;
    }

    private function createMethod()
    {
        $table = $this->createTable('methods');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('class_id', 'integer');
        $table->addColumn('name', 'string');
        $table->addColumn('doc', 'text');
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint(
            $this->classTable, ['class_id'], ['id'], ['onDelete' => 'CASCADE']
        );
        $this->methodTable = $table;
    }

    private function createParam()
    {
        $table = $this->createTable('params');
        $table->addColumn('name', 'string');
        $table->addColumn('method_id', 'integer');
        $table->addForeignKeyConstraint(
            $this->methodTable, ['method_id'], ['id'], ['onDelete' => 'CASCADE']
        );
        $this->paramTable = $table;
    }
}

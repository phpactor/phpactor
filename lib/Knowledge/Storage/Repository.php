<?php

namespace PhpActor\Knowledge\Storage;

use PhpActor\Knowledge\Reflection\ClassHierarchy;
use PhpActor\Knowledge\Reflection\ClassReflection;

class Repository
{
    private $pdo;
    private $isNew = false;

    public function __construct($path)
    {
        if (!file_exists($path)) {
            $this->isNew = true;
        }
        $this->pdo = new \PDO(
            'sqlite:' . $path
        );
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        if ($this->isNew) {
            $this->initDatabase();
        }
    }

    public function storeClassHierachy(ClassHierarchy $hierarchy)
    {
        foreach ($hierarchy->getClasses() as $classReflection) {
            $this->storeClassReflection($classReflection);
        }
    }

    private function storeClassReflection(ClassReflection $reflection)
    {
        $this->pdo->beginTransaction();
        $classData = array(
            'namespace' => $reflection->getNamespace(), 
            'name' => $reflection->getShortName(),
            'file' => $reflection->getFile(),
            'doc' => $reflection->getDoc()
        );
        $sql = sprintf(
            'INSERT INTO classes (%s) VALUES (%s)',
            implode(', ', array_keys($classData)), 
            implode(', ', array_fill(0, count($classData), '?'))
        );
        $statement = $this->pdo->prepare($sql);
        $statement->execute(array_values($classData));
        $lastId = $this->pdo->lastInsertId();

        foreach ($reflection->getMethods() as $method) {
            $methodData = array(
                'name' => $method->getName(),
                'class_id' => $lastId,
                'doc' => $method->getDoc()
            );

            $sql = sprintf(
                'INSERT INTO methods (%s) VALUES (%s)',
                implode(', ', array_keys($methodData)), 
                implode(', ', array_fill(0, count($methodData), '?'))
            );
            $statement = $this->pdo->prepare($sql);
            $statement->execute(array_values($methodData));
            $lastId = $this->pdo->lastInsertId();

            foreach ($method->getParams() as $param) {
                $paramData = array(
                    'name' => $param->getName(),
                    'method_id' => $lastId,
                );

                $sql = sprintf(
                    'INSERT INTO params (%s) VALUES (%s)',
                    implode(', ', array_keys($paramData)), 
                    implode(', ', array_fill(0, count($paramData), '?'))
                );
                $statement = $this->pdo->prepare($sql);
                $statement->execute(array_values($paramData));
            }
        }
        $this->pdo->commit();
    }

    private function initDatabase()
    {
        $this->pdo->query(<<<EOT
CREATE TABLE classes (
    id INTEGER PRIMARY KEY,
    namespace VARCHAR,
    name VARCHAR,
    file VARCHAR,
    doc VARCHAR
);
EOT
    );
        $this->pdo->query(<<<EOT
CREATE TABLE methods (
    id INTEGER PRIMARY KEY,
    class_id INTEGER,
    name VARCHAR,
    doc VARCHAR
);
EOT
        );

        $this->pdo->query(<<<EOT
CREATE TABLE params (
    id INTEGER PRIMARY KEY,
    method_id INTEGER,
    name VARCHAR,
    class VARCHAR
);
EOT
        );
    }
}

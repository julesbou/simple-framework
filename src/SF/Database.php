<?php

namespace SF;

/*
 * This file is part of the SimpleFramework
 *
 * (c) Jules Boussekeyt <jules.boussekeyt@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * The Database class represent a database table
 */
class Database
{
    /**
     * connection
     */
    protected $pdo;

    protected $tablename;
    protected $primaryKey;
    protected $primaryKeys;

    protected $stmt;

    public function __construct(\PDO $pdo, $tablename, array $primaryKeys = array('id'))
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->tablename    = $tablename;
        $this->primaryKeys  = $primaryKeys;
        $this->primaryKey   = reset($primaryKeys);
    }

    /**
     * Insert a record in the database
     */
    public function insert(array $data)
    {
        $cols = implode(',', array_keys($data));
        $vals = implode(',', array_fill(0, count($data), '?'));

        return $this->pdo
            ->prepare("INSERT INTO $this->tablename ($cols) VALUES ($vals)")
            ->execute(array_values($data));
    }

    /**
     * Update a record in the database
     */
    public function update($id, array $data)
    {
        $cols = $this->formatClause(array_keys($data), ',');
        $data[] = (int)$id;

        return $this->pdo
            ->prepare("UPDATE $this->tablename SET $cols WHERE $this->primaryKey = ?")
            ->execute(array_values($data));
    }

    /**
     * Delete a record in the database
     */
    public function delete($id)
    {
        return $this->pdo
            ->prepare("DELETE FROM $this->tablename WHERE $this->primaryKey = ?")
            ->execute(array((int) $id));
    }

    /**
     * Find a record by it's identifier
     */
    public function find($id)
    {
        $placeholders = $this->formatClause($this->primaryKeys, 'OR');
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->tablename} WHERE $placeholders");
        $stmt->execute(array_fill(0, count($this->primaryKeys), $id));

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Find a record
     */
    public function findOneBy(array $params)
    {
        return $this->findBy($params, true);
    }

    /**
     * Find some records
     */
    public function findBy(array $params, $fetchOne = false)
    {
        $query = "SELECT * FROM {$this->tablename}";

        if (!empty($params)) {
            $placeholders = $this->formatClause(array_keys($params), 'OR');
            $query .= " WHERE $placeholders";

        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_values($params));

        $fetch = $fetchOne ? 'fetch' : 'fetchAll';
        $return = $fetchOne ? null : array();

        return $stmt->$fetch(\PDO::FETCH_ASSOC) ?: $return;
    }

    /**
     * Find all records
     */
    public function findAll()
    {
        return $this->findBy(array());
    }

    private function formatClause(array $keys, $operator = 'AND')
    {
        $clause = '';

        foreach ($keys as $i => $key) {
            $clause .= ($i == 0 ? '' : $operator) . " $key = ?";
        }

        return trim($clause);
    }
}

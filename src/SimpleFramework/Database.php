<?php

namespace SimpleFramework;

abstract class Database
{
    protected $pdo;
    protected $classname;
    protected $tablename;
    protected $relations;
    protected $primaryKey;
    protected $primaryKeys;

    protected $stmt;

    public function __construct(\PDO $pdo)
    {
        // @codeCoverageIgnoreStart
        $this->pdo = $pdo;
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->classname    = $this->getClassName();
        $this->tablename    = $this->getTableName();
        $this->relations    = $this->getRelations();
        $this->primaryKeys  = $this->getPrimaryKeys();
        $this->primaryKey   = reset($this->primaryKeys);

        if (null !== $this->classname && !class_exists($this->classname)) {
            throw new \ErrorException("class {$this->classname} do not exists");
        }
    }

    protected function getClassName()
    {
        return null;
    }

    protected function getRelations()
    {
        return array();
    }
    // @codeCoverageIgnoreEnd

    abstract protected function getTableName();

    abstract protected function getPrimaryKeys();

    public function insert(array $data)
    {
        $cols = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $query = "INSERT INTO $this->tablename ($cols) VALUES ($placeholders)";

        return $this->pdo->prepare($query)->execute(array_values($data));
    }

    public function update($id, array $data, $identifier = null)
    {
        $cols = self::formatClause(array_keys($data), '?');

        $query = "UPDATE $this->tablename SET $cols WHERE $this->primaryKey = $id";

        return $this->pdo->prepare($query)->execute(array_values($data));
    }

    public function delete($id)
    {
        $query = "DELETE FROM $this->tablename WHERE $this->primaryKey = $id";

        return $this->pdo->prepare($query)->execute();
    }

    public function one()
    {
        if (null === $this->stmt) {
            throw new \LogicException('start by creating a query');
        }

        if (null !== $this->classname) {
            $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $this->classname);
        } else {
            $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
        }

        $result = $this->stmt->fetch();
        return !$result ? null : $result;
    }

    public function all()
    {
        if (null === $this->stmt) {
            throw new \LogicException('start by creating a query');
        }

        if (null !== $this->classname) {
            $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $this->classname);
        } else {
            $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
        }

        $result = $this->stmt->fetchAll();
        return !$result ? null : $result;
    }

    public function find($params = array())
    {
        if (is_array($params) && !empty($params)) {
            $where = 'WHERE '.self::formatClause(array_keys($params), 'AND', array_values($params));
        } else if (!is_array($params)) {
            $where = 'WHERE '.self::formatClause($this->primaryKeys, 'OR', array_fill(0, 5, $params));
        } else {
            $where = '';
        }

        $query = "SELECT * FROM {$this->tablename} $where";
        $this->stmt = $this->pdo->query($query);

        return $this;
    }

    public function joinOne(array $params, array $relations = array(), $join = 'LEFT')
    {
        $metadata = get_object_vars(unserialize(sprintf('O:%d:"%s":0:{}', strlen($this->classname), $this->classname)));
        $query = $this->createJoinQuery($metadata, $relations, $params, $join);

        // execute query
        $stmt = $this->pdo->query($query);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, $this->classname);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        $this->filterObject($result, array_keys($metadata), $relations);

        return $result;
    }

    public function joinMany(array $params, array $relations = array(), $join = 'LEFT')
    {
        $metadata = get_object_vars(unserialize(sprintf('O:%d:"%s":0:{}', strlen($this->classname), $this->classname)));
        $query = $this->createJoinQuery($metadata, $relations, $params, $join);

        // execute query
        $result = $this->pdo->query($query)->fetchAll(\PDO::FETCH_CLASS, $this->classname);

        $arr = array();
        foreach ($result as $res) {
            $this->filterObject($res, array_keys($metadata), $relations);
            $arr[] = $res;
        }

        return $arr;
    }

    protected function createJoinQuery(&$metadata, $relations, $params, $join)
    {
        $joins = array();

        foreach ($relations as $relation) {
            if (!isset($this->relations[$relation])) {
                throw new \OutOfBoundsException("relation '$relation' do not exists");
            }

            // collect some data about relations
            $r = $this->relations[$relation];
            unset($metadata[$r['property']]);

            if ($r['type'] !== 'one') {
                continue;
            }

            if (!class_exists($r['class'])) {
                throw new \ErrorException("relation class {$r['class']} do not exists");
            }

            // create sql for each relation
            $props = get_object_vars(unserialize(sprintf('O:%d:"%s":0:{}', strlen($r['class']), $r['class'])));
            $joins[] = "{$r['table']} {$r['table']} ON {$r['table']}.{$r['foreign']} = {$this->tablename}.{$r['local']}";
            foreach ($props as $prop => $val) {
                $selects[] = "{$r['table']}.$prop AS {$r['table']}_$prop";
            }
        }

        // select clause
        foreach ($metadata as $prop => $_) {
            $selects[] = "{$this->tablename}.$prop AS {$this->tablename}_$prop";
        }

        // pre-format query
        $selects = implode(', ', $selects);
        $joins = count($joins) > 0 ? "$join JOIN ".implode(', ', $joins) : '';
        $where = count($params) > 0 ? 'WHERE '.self::formatClause(array_keys($params), 'AND', array_values($params), $this->tablename.'.') : '';
        $query = "SELECT $selects FROM $this->tablename $joins $where";

        return $query;
    }

    protected function filterObject($object, $objProps, $relations)
    {
        // filter on each object properties
        foreach ($objProps as $prop) {
            $key = $this->tablename.'_'.$prop;
            $object->$prop = $object->$key;
        }

        // filter on each relation properties
        foreach ($relations as $relation) {
            $r = $this->relations[$relation];

            if ($r['type'] == 'many') {
                if (!class_exists($r['class'])) {
                    throw new \ErrorException("relation class {$r['class']} do not exists");
                }

                $localKey = $this->tablename.'_'.$r['local'];
                $query = "SELECT * FROM {$r['table']} WHERE {$r['foreign']} = {$object->$localKey}";
                $stmt = $this->pdo->query($query);
                $rows = $stmt->fetchAll(\PDO::FETCH_CLASS, $r['class']);
                $object->{$r['property']} = $rows;
            } else {
                $relation = unserialize(sprintf('O:%d:"%s":0:{}', strlen($r['class']), $r['class']));
                foreach ($relation as $prop => $val) {
                    $key = $r['table'].'_'.$prop;
                    if (property_exists($relation, $prop)) {
                        $relation->$prop = $object->$key;
                        unset($object->$key);
                    }
                }

                $object->{$r['property']} = $relation;
            }
        }

        // filter on each object properties
        foreach ($objProps as $prop) {
            $key = $this->tablename.'_'.$prop;
            unset($object->$key);
        }
    }

    private static function formatClause(array $keys, $operator, $values = array(), $alias = '')
    {
        if (empty($values)) {
            $values = array_fill(0, count($keys), '?');
        }

        $clause = '';
        foreach ($keys as $i => $key) {
            $val = $values[$i];
            $clause .= sprintf('%s %s = %s ',
                $i == 0 ? '' : $operator,
                $alias.$key,
                is_string($val) && '?' !== $val ? "'$val'" : $val
            );
        }
        return trim($clause);
    }
}

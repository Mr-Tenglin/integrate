<?php
/**
 * GitHub Project: Lazer - php flat file database based on JSON files
 * Copy Project Code: https://github.com/Lazer-Database/Lazer-Database
 */

declare (strict_types = 1);

namespace tenglin\integrate\handler;

use Exception;
use Lazer\Classes\Database as Lazer;
use Lazer\Classes\Helpers\Validate;

class Jsondata
{
    protected $prefix = 'ejcms_';
    protected $tableName = '';
    protected $where = [];
    protected $orderby = [];

    public function __construct($args = [])
    {
        if (!empty($args[0])) {
            define('LAZER_DATA_PATH', $args[0]);
        }
    }

    public function createTable($data)
    {
        try {
            if (!is_dir(LAZER_DATA_PATH)) {
                dir_create(LAZER_DATA_PATH);
            }
            Lazer::create($this->tableName, $data);
            $this->tableName = '';
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function removeTable()
    {
        try {
            Lazer::remove($this->tableName);
            $this->tableName = '';
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function existsTable($boolean = true)
    {
        try {
            Validate::table($this->tableName)->exists();
            $this->tableName = '';
            return true;
        } catch (Exception $e) {
            if ($boolean) {
                return false;
            }
            throw new Exception($e->getMessage());
        }
    }

    public function prefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function table($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function name($tableName)
    {
        $this->tableName = $this->prefix . $tableName;
        return $this;
    }

    public function where($prop, $operator = '=', $value = '', $cond = 'AND')
    {
        $whereList = [];
        if (is_array($prop)) {
            if (count($prop) == count($prop, 1)) {
                foreach ($prop as $k => $v) {
                    $whereList[] = [$cond, $k, $operator, $v];
                }
            } else {
                foreach ($prop as $v) {
                    $whereList[] = [empty($v[3]) ? 'AND' : $v[3], $v[0], $v[1], $v[2]];
                }
            }
        } else {
            if (empty($value)) {
                $whereList[] = [$cond, $prop, '=', $operator];
            } else {
                $whereList[] = [$cond, $prop, $operator, $value];
            }
        }
        foreach ($whereList as $v) {
            if (strpos($v[1], '|') !== false) {
                $i = 0;
                foreach (explode('|', $v[1]) as $f) {
                    if ($i === 0) {
                        $this->where[] = [$v[0], $f, $v[2], $v[3]];
                    } else {
                        $this->where[] = ['OR', $f, $v[2], $v[3]];
                    }
                }
            } else {
                $this->where[] = $v;
            }
        }
        return $this;
    }

    public function whereOr($prop, $operator = '=', $value = '')
    {
        $this->where($prop, $operator, $value, 'OR');
        return $this;
    }

    public function whereLike($prop, $value)
    {
        $this->where($prop, 'LIKE', $value);
        return $this;
    }

    public function whereNotLike($prop, $value)
    {
        $this->where($prop, 'NOT LIKE', $value);
        return $this;
    }

    public function whereIn($prop, $value)
    {
        $this->where($prop, 'IN', $value);
        return $this;
    }

    public function whereNotIn($prop, $value)
    {
        $this->where($prop, 'NOT IN', $value);
        return $this;
    }

    public function whereBetween($prop, $value)
    {
        $this->where($prop, 'BETWEEN', $value);
        return $this;
    }

    public function whereNotBetween($prop, $value)
    {
        $this->where($prop, 'NOT BETWEEN', $value);
        return $this;
    }

    public function whereTime($prop, $operator, $value)
    {
        $this->where($prop, $operator, $value);
        return $this;
    }

    public function order($field, $direction = 'DESC')
    {
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                $this->orderby[$k] = $v;
            }
        } else {
            $this->orderby[$field] = $direction;
        }
        return $this;
    }

    public function find()
    {
        $db = $this->rawQuery();
        if ($data = $db->find()->asArray()) {
            if (empty($data[0])) {
                return $data;
            } else {
                return $data[0];
            }
        } else {
            return [];
        }
    }

    public function select($key = null, $value = null)
    {
        $db = $this->rawQuery();
        if ($data = $db->findAll()) {
            return $data->asArray($key, $value);
        } else {
            return [];
        }
    }

    public function paginate($number, $offset = 0)
    {
        $db = $this->rawQuery();
        $total = $this->total($db);
        if ($offset == 0) {
            $per_page = $number;
            $current_page = 1;
        } else {
            $per_page = $offset;
            $current_page = $number;
        }
        $last_page = ceil($total / $per_page);
        $db->limit(($current_page * $per_page) - 1, $per_page);
        return [
            'total' => (int) $total,
            'per_page' => (int) $per_page,
            'current_page' => (int) $current_page,
            'last_page' => (int) $last_page,
            'data' => $db->findAll()->asArray(),
        ];
    }

    public function insert($data)
    {
        try {
            $db = $this->rawQuery();
            foreach ($data as $k => $v) {
                $db->{$k} = $v;
            }
            $db->save();
            return $db->lastId();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function insertAll($data)
    {
        try {
            $ids = [];
            $db = $this->rawQuery();
            foreach ($data as $group) {
                foreach ($group as $k => $v) {
                    $db->{$k} = $v;
                }
                $db->save();
                $ids[] = $db->lastId();
            }
            return $ids;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function update($data)
    {
        try {
            $db = $this->rawQuery();
            $db->find();
            foreach ($data as $k => $v) {
                $db->{$k} = $v;
            }
            return $db->save();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function delete()
    {
        try {
            $db = $this->rawQuery();
            $db->find();
            return $db->delete();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function rawQuery()
    {
        $db = Lazer::table($this->tableName);
        $i = 0;
        foreach ($this->where as $cond) {
            list($concat, $varName, $operator, $val) = $cond;
            $concat = strtoupper($concat);
            $operator = strtoupper($operator);
            if ($i === 0) {
                $db->where($varName, $operator, $val);
            } else {
                if ($concat == 'AND') {
                    $db->andWhere($varName, $operator, $val);
                } else {
                    $db->orWhere($varName, $operator, $val);
                }
            }
            $i++;
        }
        foreach ($this->orderby as $k => $v) {
            $db->orderBy($k, $v);
        }
        $this->tableName = '';
        $this->where = [];
        $this->orderby = [];
        return $db;
    }

    private function total($db)
    {
        return $db->findAll()->count();
    }
}

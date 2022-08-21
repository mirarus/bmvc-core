<?php

/**
 * ModelTree
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 0.10
 */

namespace BMVC\Core;

use Mirarus\DB\DB;

abstract class ModelTree
{

  /**
   * @var string
   */
  protected $tableName = "";

  /**
   * @var string
   */
  protected $whereMark = "=";

  /**
   * @param string|null $tableName
   */
  public function __construct(string $tableName = null)
  {
    if ($tableName) $this->tableName = $tableName;
  }

  /**
   * @return DB|never|void
   */
  public function DB()
  {
    return Model::DB();
  }

  /**
   * @param string|null $key
   * @param $val
   * @param bool $all
   * @param string|null $sKey
   * @param int $sVal
   * @return mixed
   */
  public function get(string $key = null, $val = null, bool $all = false, string $sKey = null, int $sVal = 1)
  {
    $arr = [];

    if ($val) $arr = [($key ? $key : 'id') => $val];
    if ($sKey) $arr = array_merge($arr, [($sKey ? $sKey : 'status') => $sVal]);

    return $this->wget($arr, $all);
  }

  /**
   * @param $where
   * @param bool $all
   * @return mixed
   */
  public function wget($where, bool $all = false, string $sortColumn = null, string $sortType = "ASC")
  {
    $sql = $this->DB()->from($this->tableName);

    $this->_where($sql, $where);
    if ($sortColumn) $this->DB()->orderBy($sortColumn, $sortType);

    return $all ? $sql->all() : $sql->first();
  }

  /**
   * @return mixed
   */
  public function all(string $sortColumn = null, string $sortType = "ASC")
  {
    return $this->wget([], true, $sortColumn, $sortType);
  }

  /**
   * @param array $data
   * @return int
   */
  public function add(array $data): int
  {
    return $this->DB()
      ->insert($this->tableName)
      ->set(array_merge($data, [
        'time' => time()
      ]));
  }

  /**
   * @param string $key
   * @param $val
   * @param array $data
   * @return bool
   */
  public function edit(string $key, $val, array $data): bool
  {
    return $this->wedit([$key => $val], $data);
  }

  /**
   * @param $where
   * @param array $data
   * @return bool
   */
  public function wedit($where, array $data): bool
  {
    if ($this->wget($where)) {

      $sql = $this->DB()->update($this->tableName);
      $this->_where($sql, $where);

      return $sql->set(array_merge($data, [
        'edit_time' => time()
      ]));
    }
    return false;
  }

  /**
   * @param string $key
   * @param $val
   * @return bool
   */
  public function delete(string $key, $val): bool
  {
    return $this->wdelete([$key => $val]);
  }

  /**
   * @param $where
   * @return bool
   */
  public function wdelete($where): bool
  {
    if ($this->wget($where)) {

      $sql = $this->DB()->delete($this->tableName);
      $this->_where($sql, $where);

      return $sql->done();
    }
    return false;
  }

  /**
   * @param string|null $key
   * @param $val
   * @return int
   */
  public function count(string $key = null, $val = null): int
  {
    return $this->wcount(($val ? [($key ? $key : 'id') => $val] : null));
  }

  /**
   * @param $where
   * @return int
   */
  public function wcount($where): int
  {
    if ($this->wget($where)) {

      $sql = $this->DB()->from($this->tableName);
      $this->_where($sql, $where);

      return $sql->rowCount();
    }
    return false;
  }

  /**
   * @param $sql
   * @param array|null $where
   * @return void
   */
  public function _where(&$sql, array $where = null)
  {
    if ($sql && $where) {
      array_map(function ($key, $value) use ($sql) {
        if ($key && $value) $sql->where($key, $value, $this->whereMark);
      }, array_keys($where), array_values($where));
    }
  }

  /**
   * @return string
   */
  public function getTableName(): string
  {
    return $this->tableName;
  }

  /**
   * @param string $tableName
   */
  public function setTableName(string $tableName): void
  {
    $this->tableName = $tableName;
  }

  /**
   * @return string
   */
  public function getWhereMark(): string
  {
    return $this->whereMark;
  }

  /**
   * @param string $whereMark
   */
  public function setWhereMark(string $whereMark = "="): void
  {
    $this->whereMark = $whereMark;
  }

  /**
   * @param string $method
   * @param array $parameters
   * @return mixed
   */
  public static function __callStatic(string $method, array $parameters)
  {
    $class = get_called_class();
    $array = explode('_', $method);
    $method = array_pop($array);
    return (new $class)->$method(...$parameters);
  }
}
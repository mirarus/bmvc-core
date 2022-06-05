<?php

/**
 * ModelTree
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 0.4
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
   * @param bool $status
   * @param string|null $sKey
   * @param int $sVal
   * @return mixed
   */
  public function get(string $key = null, $val = null, bool $all = false, bool $status = false, string $sKey = null, int $sVal = 1)
  {
    $arr = [];

    if ($val) $arr = [($key ? $key : 'id') => $val];
    if ($status) $arr = array_merge($arr, [($sKey ? $sKey : 'status') => $sVal]);

    return $this->wget($arr, $all);
  }

  /**
   * @param $where
   * @param bool $all
   * @return mixed
   */
  public function wget($where, bool $all = false)
  {
    $sql = $this->DB()->from($this->tableName);

    $this->_where($sql, $where);

    return $all ? $sql->all() : $sql->first();
  }

  /**
   * @param array $data
   * @return bool
   */
  public function add(array $data): bool
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
    return $this->wedit([$key, $val], $data);
  }

  /**
   * @param $where
   * @param array $data
   * @return bool
   */
  public function wedit($where, array $data): bool
  {
    $sql = $this->DB()->update($this->tableName);
    $this->_where($sql, $where);

    return $sql->set(array_merge($data, [
      'edit_time' => time()
    ]));
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
    $sql = $this->DB()->delete($this->tableName);
    $this->_where($sql, $where);

    return $sql->done();
  }

  /**
   * @param string|null $key
   * @param $val
   * @return mixed
   */
  public function count(string $key = null, $val = null)
  {
    return $this->wcount(($val ? [($key ? $key : 'id') => $val] : null));
  }

  /**
   * @param $where
   * @return mixed
   */
  public function wcount($where)
  {
    $sql = $this->DB()->from($this->tableName);
    $this->_where($sql, $where);

    return $sql->rowCount() ?: false;
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
        if ($key && $value) $sql->where($key, $value);
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
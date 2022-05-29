<?php

/**
 * ModelTree
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 0.0
 */

namespace BMVC\Core;

use Mirarus\DB\DB;

class ModelTree
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
   * @return array
   */
  public function wget($where, bool $all = false): array
  {
    $sql = Model::DB()->from($this->tableName);

    $this->_where($sql, $where);

    return $all ? $sql->all() : $sql->first();
  }

  /**
   * @param array $data
   * @return bool
   */
  public function add(array $data): bool
  {
    return Model::DB()
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
    $sql = Model::DB()->update($this->tableName);
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
    $sql = Model::DB()->delete($this->tableName);
    $this->_where($sql, $where);

    return $sql->done();
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
    $sql = Model::DB()->from($this->tableName);
    $this->_where($sql, $where);

    return $sql->rowCount();
  }

  /**
   * @param $sql
   * @param array|null $where
   * @return void
   */
  private function _where(&$sql, array $where = null)
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
}
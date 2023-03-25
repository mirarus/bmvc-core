<?php

/**
 * ModelTree
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 0.12
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
	 * @param array $where
	 * @param string|null $query
	 * @return mixed
	 */
	public function get(string $key = null, $val = null, bool $all = false, array $where = [], string $query = null)
	{
		$arr = [];

		if ($val) $arr = [($key ?: 'id') => $val];
		$arr = array_merge($arr, $where);

		return $this->wGet($arr, $all, [], $query);
	}

	/**
	 * @param $where
	 * @param bool $all
	 * @param array $sort
	 * @param string|null $query
	 * @return mixed
	 */
	public function wGet($where, bool $all = false, array $sort = [null, "ASC"], string $query = null)
	{
		$sql = $this->DB()->from($this->tableName);

		$this->_where($sql, $where);

		if ($sort != null && $sort[0] != null && $sort[1] != null) $this->DB()->orderBy($sort[0], $sort[1]);
		if ($query) $sql = $sql->sql(' ' . $query);

		return $all ? $sql->all() : $sql->first();
	}

	/**
	 * @param array $sort
	 * @param string|null $query
	 * @return mixed
	 */
	public function all(array $sort = [null, "ASC"], string $query = null)
	{
		return $this->wGet([], true, $sort, $query);
	}

	/**
	 * @param array $data
	 * @param bool $time
	 * @return int
	 */
	public function add(array $data, bool $time = true): int
	{
		return $this->DB()
			->insert($this->tableName)
			->set(array_merge($data, $time ? [
				'time' => time()
			] : []));
	}

	/**
	 * @param string $key
	 * @param $val
	 * @param array $data
	 * @param bool $time
	 * @return bool
	 */
	public function edit(string $key, $val, array $data, bool $time = true): bool
	{
		return $this->wEdit([$key => $val], $data, $time);
	}

	/**
	 * @param $where
	 * @param array $data
	 * @param bool $time
	 * @return bool
	 */
	public function wEdit($where, array $data, bool $time = true): bool
	{
		if ($this->wGet($where)) {

			$sql = $this->DB()->update($this->tableName);
			$this->_where($sql, $where);

			return $sql->set(array_merge($data, $time ? [
				'edit_time' => time()
			] : []));
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
		return $this->wDelete([$key => $val]);
	}

	/**
	 * @param $where
	 * @return bool
	 */
	public function wDelete($where): bool
	{
		if ($this->wGet($where)) {

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
		return $this->wCount(($val ? [($key ? $key : 'id') => $val] : null));
	}

	/**
	 * @param $where
	 * @return int
	 */
	public function wCount($where): int
	{
		if ($this->wGet($where)) {

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
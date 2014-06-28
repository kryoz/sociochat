<?php
namespace Sociochat\DAO;

use Sociochat\DB;
use Sociochat\DI;
use Sociochat\FixedArrayAccess;

abstract class DAOBase extends FixedArrayAccess
{
	const ID = 'id';

	/**
	 * @var DB
	 */
	protected $db;
	protected $relativeProperties = [];
	protected $dbTable;
	protected $types = [];

	public function __construct($propertyNames = null)
	{
		$properties = [self::ID];
		$properties = array_merge($properties, $propertyNames);
		$this->db = DI::get()->container()->get('db');

		parent::__construct($properties);
	}

	public static function create()
	{
		return new static();
	}

	public function getById($id)
	{
		$query = "SELECT * FROM {$this->dbTable} WHERE id = :".static::ID;
		if ($data = $this->db->query($query, [static::ID => $id])) {
			$this->fillParams($data[0]);
		}

		return $this;
	}

	public function fillParams(array $params)
	{
		foreach ($params as $property => $value) {
			$this[$property] = $value;
		}
	}

	public function getId()
	{
		return $this[self::ID];
	}

	public function save()
	{
		$params = array_diff_key($this->properties, $this->relativeProperties + [self::ID => null]);
		$sequence = null;

		if (!$this->getId()) {
			$keys = array_keys($params);

			$query = "INSERT INTO {$this->dbTable} (".implode(', ', $keys).") VALUES ";

			foreach ($keys as &$key) {
				$key = ':'.$key;
			}
			$query .= "(".implode(', ', $keys). ")";
			$sequence = $this->dbTable.'_id_seq';
		} else {
			$query = "UPDATE {$this->dbTable} SET ";
			$queryParts = [];

			foreach ($params as $key => $val) {
				$queryParts[] = "$key = :{$key}";
			}

			$query .= implode(", ", $queryParts). " WHERE id = :".self::ID;
			$params += [self::ID => $this->getId()];
		}

		$this[self::ID] = $this->db->exec($query, $params, $sequence, $this->types);

		$this->flushRelatives($this->getForeignProperties());
	}

	public function getAllList()
	{
		$query = "SELECT * FROM {$this->dbTable}";

		return $this->getListByQuery($query);
	}

	/**
	 *
	 * @param string $query
	 * @param array $params
	 * @param string null|string
	 * @return array
	 */
	public function getListByQuery($query, array $params = [], $type = null)
	{
		$result = [];

		foreach ($this->db->query($query, $params) as $item) {
			if ($type) {
				$entity = new $type;
			} else {
				$entity = static::create();
			}
			$entity->fillParams($item);
			$result[] = $entity;
		}

		return $result;
	}

	public function dropById($id, $dbKey = null)
	{
		$dbKey = $dbKey ?: 'id';
		$this->db->exec("DELETE FROM {$this->dbTable} WHERE $dbKey = :id", ['id' => $id]);
	}

	public function __wakeup()
	{
		$this->db = DI::get()->container()->get('db');
	}

	public function __sleep()
	{
		return array('properties', 'propertyNames');
	}

	protected function addRelativeProperty($propertyName)
	{
		$this->addProperty($propertyName);
		$this->relativeProperties = array_merge($this->relativeProperties, [$propertyName => null]);
	}

	protected function getByPropId($propName, $id)
	{
		if (!in_array($propName, $this->propertyNames)) {
			throw new \Exception('Undefined property name '.$propName);
		}

		$query = "SELECT * FROM {$this->dbTable} WHERE $propName = :$propName";
		if ($data = $this->db->query($query, [$propName => $id])) {
			$this->fillParams($data[0]);
		}

		return $this;
	}

	protected function flushProperty($propName)
	{
		if (!in_array($propName, $this->propertyNames)) {
			throw new \Exception('Undefined property name '.$propName);
		}

		$this[$propName] = null;
	}

	protected function flushRelatives(array $properties)
	{
		foreach ($properties as $prop) {
			$this->flushProperty($prop);
		}
	}

	abstract protected function getForeignProperties();
}

<?php namespace Congredi\Rbac\Adapters;


abstract class AbstractDatabaseAdapter implements DatabaseAdapterInterface
{
	/**
	 * @var string the name of the table storing rbac items
	 */
	public $itemTable = 'rbac_item';
	/**
	 * @var string the name of the table storing rbac item hierarchy
	 */
	public $itemChildTable = 'rbac_item_child';
	/**
	 * @var string the name of the table storing rbac item assignments.
	 */
	public $assignmentTable = 'rbac_assignment';
	/**
	 * @var string the name of the table storing rules.
	 */
	public $ruleTable = 'rbac_rule';

	/**
	 * The connection resolver instance.
	 */
	protected $resolver;
	/**
	 * The connection name.
	 *
	 * @var string
	 */
	protected $connectionName;

	/**
	 * @return mixed
	 */
	public function getResolver()
	{
		return $this->resolver;
	}

	/**
	 * @param $resolver
	 */
	public function setResolver($resolver)
	{
		$this->resolver = $resolver;
	}

	/**
	 * @param $connectionName
	 */
	public function setConnectionName($connectionName)
	{
		$this->connectionName = $connectionName;
	}

	/**
	 * @return mixed
	 */
	abstract public function getConnection();

	/**
	 * Returns a value indicating whether the database supports cascading update and delete.
	 *
	 * @return bool
	 */
	public function supportsCascadeUpdate()
	{
		return strncmp($this->getDriverName(), 'sqlite', 6) !== 0;
	}

	/**
	 * @return mixed
	 */
	abstract public function getDriverName();
}
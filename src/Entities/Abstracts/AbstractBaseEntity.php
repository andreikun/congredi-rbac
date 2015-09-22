<?php namespace Congredi\Rbac\Entities\Abstracts;

use Congredi\Rbac\Adapters\DatabaseAdapterInterface;

abstract class AbstractBaseEntity
{
	/**
	 * @var \Congredi\Rbac\Adapters\DatabaseAdapterInterface
	 */
	protected $databaseAdapter;

	public function __construct(DatabaseAdapterInterface $databaseAdapter)
	{
		$this->databaseAdapter = $databaseAdapter;
	}

	/**
	 * @param \Congredi\Rbac\Adapters\DatabaseAdapterInterface $databaseAdapter
	 * @return static
	 */
	public static function create(DatabaseAdapterInterface $databaseAdapter)
	{
		return new static($databaseAdapter);
	}

	/**
	 * Returns the fully qualified name of this class.
	 *
	 * @return string the fully qualified name of this class.
	 */
	public static function className()
	{
		return static::class;
	}

	/**
	 * @param array $properties
	 */
	public function fill($properties = [])
	{
		foreach ($properties as $name => $value) {
			$this->$name = $value;
		}

		return $this;
	}
}
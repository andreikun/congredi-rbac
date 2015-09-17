<?php namespace Congredi\Rbac\Entities\Abstracts;

use Congredi\Rbac\Entities\Interfaces\ItemInterface;
use Congredi\Rbac\Exceptions\InvalidCallException;
use Congredi\Rbac\Exceptions\InvalidParamException;

abstract class AbstractItem extends AbstractBaseEntity implements ItemInterface
{
	const TYPE_ROLE = 1;
	const TYPE_PERMISSION = 2;

	/**
	 * @var integer
	 */
	public $type;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var string rule
	 */
	public $ruleName;

	/**
	 * @var mixed representing data associated with this item
	 */
	public $data;

	/**
	 * @var integer UNIX timestamp
	 */
	public $createdAt;

	/**
	 * @var integer UNIX timestamp
	 */
	public $updatedAt;

	/**
	 * @param \Congredi\Rbac\Entities\Interfaces\ItemInterface $item
	 * @return $this
	 * @throws \Congredi\Rbac\Exceptions\InvalidCallException
	 * @throws \Congredi\Rbac\Exceptions\InvalidParamException
	 */
	public function addChild(ItemInterface $item)
	{
		if ($this->name === $item->name) {
			throw new InvalidParamException("Cannot add '{$this->name}' as a child of itself.");
		}

		if ($this->type == self::TYPE_PERMISSION && $item->type == self::TYPE_ROLE) {
			throw new InvalidParamException("Cannot add a role as a child of a permission.");
		}

		if ($this->detectLoop($item)) {
			throw new InvalidCallException("Cannot add '{$item->name}' as a child of '{$this->name}'. A loop has been detected.");
		}

		$this->databaseAdapter->addChild($this, $item);

		return $this;
	}

	/**
	 * @param \Congredi\Rbac\Entities\Interfaces\ItemInterface $item
	 * @return $this
	 */
	public function removeChild(ItemInterface $item)
	{
		$this->databaseAdapter->removeChild($this, $item);

		return $this;
	}

	/**
	 * @return $this
	 */
	public function removeChildren()
	{
		$this->databaseAdapter->removeChildren($this);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function hasChild(ItemInterface $item)
	{
		return count($this->databaseAdapter->getItemChild($this, $item)) > 0;
	}

	/**
	 * @param \Congredi\Rbac\Entities\Interfaces\ItemInterface $item
	 * @return bool
	 */
	protected function detectLoop(ItemInterface $item)
	{
		if ($item->name === $this->name) {
			return true;
		}

		foreach ($this->getChildren($item->name) as $grandchild) {
			if ($this->detectLoop($grandchild)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $name
	 * @return array
	 */
	protected function getChildren($name)
	{
		$children = $this->databaseAdapter->getChildrenByName($name);

		$results = [];
		foreach ($children as $child) {
			$results[$child->name] = $child;
		}

		return $results;
	}
}
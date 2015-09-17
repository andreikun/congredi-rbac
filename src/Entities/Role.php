<?php namespace Congredi\Rbac\Entities;

use Congredi\Rbac\Entities\Abstracts\AbstractRole;

class Role extends AbstractRole
{
	/**
	 * @return $this
	 */
	public function save()
	{
		$this->databaseAdapter->addItem($this);

		return $this;
	}

	/**
	 * @param $name
	 * @return $this
	 */
	public function update($name)
	{
		$this->databaseAdapter->updateItem($this, $name);

		return $this;
	}

	/**
	 * @return $this
	 */
	public function remove()
	{
		$this->databaseAdapter->removeItem($this);

		return $this;
	}

	/**
	 * @param $userId
	 * @return $this
	 */
	public function revokeFrom($userId)
	{
		if (empty($userId)) {
			return $this;
		}

		$this->databaseAdapter->removeAssignment($this, $userId);

		return $this;
	}

	/**
	 * @param $userId
	 * @return $this
	 */
	public function assignTo($userId)
	{
		$this->databaseAdapter->addAssignment($this, $userId);

		return $this;
	}
}
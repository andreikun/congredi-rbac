<?php namespace Congredi\Rbac\Entities;

use Congredi\Rbac\Entities\Abstracts\AbstractPermission;

class Permission extends AbstractPermission
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
}
<?php namespace Congredi\Rbac\Entities;

use Congredi\Rbac\Entities\Abstracts\AbstractRule;

abstract class Rule extends AbstractRule
{
	/**
	 * @return $this
	 */
	public function save()
	{
		$this->databaseAdapter->addRule($this);

		return $this;
	}

	/**
	 * @param $name
	 * @return $this
	 */
	public function update($name)
	{
		$this->databaseAdapter->updateRule($this, $name);

		return $this;
	}

	/**
	 * @return $this
	 */
	public function remove()
	{
		$this->databaseAdapter->removeRule($this);

		return $this;
	}
}
<?php namespace Congredi\Rbac\Entities\Abstracts;

use Congredi\Rbac\Entities\Interfaces\RuleInterface;

abstract class AbstractRule extends AbstractBaseEntity implements RuleInterface
{
	/**
	 * @var string name
	 */
	public $name;

	/**
	 * @var integer UNIX timestamp
	 */
	public $createdAt;

	/**
	 * @var integer UNIX timestamp
	 */
	public $updatedAt;

	/**
	 * @return array
	 */
	public function __sleep() {
		return ['name'];
	}
}
<?php namespace Congredi\Rbac\Entities\Abstracts;

use Congredi\Rbac\Entities\Interfaces\RoleInterface;

abstract class AbstractRole extends AbstractItem implements RoleInterface
{
	/**
	 * @inheritdoc
	 */
	public $type = self::TYPE_ROLE;
}
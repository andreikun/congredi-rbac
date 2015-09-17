<?php namespace Congredi\Rbac\Entities\Abstracts;


abstract class AbstractPermission extends AbstractItem
{
	/**
	 * @inheritdoc
	 */
	public $type = self::TYPE_PERMISSION;
}
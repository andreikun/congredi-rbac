<?php namespace Congredi\Rbac\Entities\Abstracts;

use Congredi\Rbac\Entities\Interfaces\AssignmentInterface;

abstract class AbstractAssignment extends AbstractBaseEntity implements AssignmentInterface
{
	/**
	 * @var string|integer user ID
	 */
	public $userId;

	/**
	 * @return string the role name
	 */
	public $roleName;

	/**
	 * @var string timestamp representing the assignment creation time
	 */
	public $createdAt;
}
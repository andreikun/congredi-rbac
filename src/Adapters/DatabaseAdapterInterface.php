<?php namespace Congredi\Rbac\Adapters;

use Congredi\Rbac\Entities\Abstracts\AbstractItem;
use Congredi\Rbac\Entities\Abstracts\AbstractRole;
use Congredi\Rbac\Entities\Rule;

interface DatabaseAdapterInterface
{
	/**
	 * @return mixed
	 */
	public function getResolver();

	/**
	 * @param $resolver
	 * @return mixed
	 */
	public function setResolver($resolver);

	/**
	 * @param $connectionName
	 * @return mixed
	 */
	public function setConnectionName($connectionName);

	/**
	 * @return mixed
	 */
	public function getConnection();

	/**
	 * @return mixed
	 */
	public function getDriverName();

	/**
	 * @return mixed
	 */
	public function supportsCascadeUpdate();

	/**
	 * @param $userId
	 * @param null $type
	 * @return mixed
	 */
	public function getItemsByUser($userId, $type = null);

	/**
	 * @return mixed
	 */
	public function getRules();

	/**
	 * @param array $names
	 * @param null $type
	 * @return mixed
	 */
	public function getItemsByNames($names = [], $type = null);

	/**
	 * @param $userId
	 * @return mixed
	 */
	public function getAssignmentsByUser($userId);

	/**
	 * @return mixed
	 */
	public function getAllItemChildren();

	/**
	 * @param $userId
	 * @param $roleName
	 * @return mixed
	 */
	public function getAssignmentByUserAndRole($userId, $roleName);

	/**
	 * @param $userId
	 * @return mixed
	 */
	public function deleteAssignmentsForUser($userId);

	/**
	 * @param $name
	 * @param null $type
	 * @return mixed|static
	 */
	public function getItemByName($name, $type = null);

	/**
	 * @param $type
	 * @return array|static[]
	 */
	public function getItemsByType($type);

	/**
	 * @param $name
	 * @return mixed|static
	 */
	public function getRuleByName($name);

	/**
	 * @param $itemName
	 * @return array
	 */
	public function getParentsForChild($itemName);

	/**
	 * @param \Congredi\Rbac\Entities\Abstracts\AbstractItem $item
	 * @return mixed
	 */
	public function addItem(AbstractItem $item);

	/**
	 * @param \Congredi\Rbac\Entities\Abstracts\AbstractItem $item
	 * @return mixed
	 */
	public function removeItem(AbstractItem $item);

	/**
	 * @param \Congredi\Rbac\Entities\Abstracts\AbstractItem $item
	 * @param $name
	 * @return mixed
	 */
	public function updateItem(AbstractItem $item, $name);

	/**
	 * @param \Congredi\Rbac\Entities\Rule $rule
	 * @return mixed
	 */
	public function addRule(Rule $rule);

	/**
	 * @param \Congredi\Rbac\Entities\Rule $rule
	 * @param $name
	 * @return mixed
	 */
	public function updateRule(Rule $rule, $name);

	/**
	 * @param \Congredi\Rbac\Entities\Rule $rule
	 * @return mixed
	 */
	public function removeRule(Rule $rule);

	/**
	 * @param $parentItem
	 * @param $childItem
	 * @return mixed
	 */
	public function addChild($parentItem, $childItem);

	/**
	 * @param $parentItem
	 * @param $childItem
	 * @return mixed
	 */
	public function removeChild($parentItem, $childItem);

	/**
	 * @param $parentItem
	 * @return mixed
	 */
	public function removeChildren($parentItem);

	/**
	 * @param $name
	 * @return mixed
	 */
	public function getChildrenByName($name);

	/**
	 * @param $parent
	 * @param $child
	 * @return mixed
	 */
	public function getItemChild($parent, $child);

	/**
	 * @param \Congredi\Rbac\Entities\Abstracts\AbstractRole $role
	 * @param $userId
	 * @return mixed
	 */
	public function addAssignment(AbstractRole $role, $userId);

	/**
	 * @param \Congredi\Rbac\Entities\Abstracts\AbstractRole $role
	 * @param $userId
	 * @return mixed
	 */
	public function removeAssignment(AbstractRole $role, $userId);

	/**
	 * @return mixed
	 */
	public function deleteAllRules();

	/**
	 * @return mixed
	 */
	public function deleteAllItems();

	/**
	 * @return mixed
	 */
	public function deleteAllAssignments();

	/**
	 * @return mixed
	 */
	public function deleteAllParentChildAssoc();

	/**
	 * @param $type
	 * @return mixed
	 */
	public function removeAllItems($type);
}
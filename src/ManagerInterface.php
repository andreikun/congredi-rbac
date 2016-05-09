<?php namespace Congredi\Rbac;

interface ManagerInterface
{
	/**
	 * Checks if the user has the specified permission.
	 *
	 * @param $userId
	 * @param $permissionName
	 * @param array $params
	 * @return mixed
	 */
	public function checkAccess($userId, $permissionName, $params = []);

	/**
	 * Creates a new Role object.
	 *
	 * @param $name
	 * @return mixed
	 */
	public function createRole($name);

	/**
	 * Returns the named role.
	 *
	 * @param $name
	 * @return mixed
	 */
	public function getRole($name);

	/**
	 * Returns all roles in the system.
	 *
	 * @return mixed
	 */
	public function getRoles();

	/**
	 * Returns the roles that are assigned to the user.
	 *
	 * @param $userId
	 * @return mixed
	 */
	public function getRolesByUser($userId);

	/**
	 *  Creates a new Permission object.
	 *
	 * @param $name
	 * @return mixed
	 */
	public function createPermission($name);

	/**
	 * Returns the named permission.
	 *
	 * @param $name
	 * @return mixed
	 */
	public function getPermission($name);

	/**
	 * Returns all permissions in the system.
	 *
	 * @return mixed
	 */
	public function getPermissions();

	/**
	 * Returns all permissions that the specified role represents.
	 *
	 * @param $roleName
	 * @return mixed
	 */
	public function getPermissionsByRole($roleName);

	/**
	 * Returns all permissions that the user has.
	 *
	 * @param $userId
	 * @return mixed
	 */
	public function getPermissionsByUser($userId);

	/**
	 * Returns the rule of the specified name.
	 *
	 * @param $name
	 * @return mixed
	 */
	public function getRule($name);

	/**
	 * Returns all rules available in the system.
	 *
	 * @return mixed
	 */
	public function getRules();

	/**
	 * Revokes all roles from a user.
	 *
	 * @param $userId
	 * @return mixed
	 */
	public function revokeAll($userId);

	/**
	 * Returns the assignment information regarding a role and a user.
	 *
	 * @param $roleName
	 * @param $userId
	 * @return mixed
	 */
	public function getAssignment($roleName, $userId);

	/**
	 * Returns all role assignment information for the specified user.
	 *
	 * @param $userId
	 * @return mixed
	 */
	public function getAssignments($userId);

	/**
	 * Returns all parent permissions and roles for a permission or role.
	 *
	 * @param $itemName
	 * @return mixed
	 */
	public function getParents($itemName);

	/**
	 * @param $item object The auth item type (either AbstractItem::TYPE_PERMISSION or AbstractItem::TYPE_ROLE)
	 * @return mixed Returns the item including a 'parents' attribute, containing the parents recursive.
	 */
	public function getParentItemsRecursive($item);

	/**
	 * @param $item object The auth item type (either AbstractItem::TYPE_PERMISSION or AbstractItem::TYPE_ROLE)
	 * @return mixed Returns the item including a 'children' attribute, containing the children recursive.
	 */
	public function getChildItemsRecursive($item);

}
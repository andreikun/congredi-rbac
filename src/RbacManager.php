<?php namespace Congredi\Rbac;

use Congredi\Rbac\Adapters\DatabaseAdapterInterface;
use Congredi\Rbac\Entities\Abstracts\AbstractItem;
use Congredi\Rbac\Entities\Assignment;
use Congredi\Rbac\Entities\Interfaces\RuleInterface;
use Congredi\Rbac\Entities\Permission;
use Congredi\Rbac\Entities\Role;
use Congredi\Rbac\Exceptions\InvalidConfigException;

class RbacManager implements ManagerInterface
{
	/**
	 * @var array
	 */
	protected $items;

	/**
	 * @var array
	 */
	protected $rules;

	/**
	 * @var array auth item parent-child relationships (childName => list of parents)
	 */
	protected $parents;

	/**
	 * @var \Congredi\Rbac\Adapters\DatabaseAdapterInterface
	 */
	protected $databaseAdapter;

	public function __construct(DatabaseAdapterInterface $databaseAdapter)
	{
		$this->databaseAdapter = $databaseAdapter;
	}

	/**
	 * @inheritdoc
	 */
	public function createRole($name)
	{
		$role = Role::create($this->databaseAdapter);
		$role->name = $name;

		return $role;
	}

	/**
	 * @param $name
	 * @return static
	 */
	public function createPermission($name)
	{
		$permission = Permission::create($this->databaseAdapter);
		$permission->name = $name;

		return $permission;
	}

	/**
	 * @param $userId
	 * @return array
	 */
	public function getRolesByUser($userId)
	{
		if (empty($userId)) {
			return [];
		}

		$roles = $this->databaseAdapter->getItemsByUser($userId, AbstractItem::TYPE_ROLE);

		$results = [];
		foreach ($roles as $role) {
			$results[$role->name] = $this->populateItem($role);
		}

		return $results;
	}

	/**
	 * Populates an auth item with the data fetched from database
	 *
	 * @param $row
	 * @return mixed
	 */
	protected function populateItem($row)
	{
		$class = $row->type == AbstractItem::TYPE_PERMISSION ? Permission::className() : Role::className();

		if (!isset($row->data) || ($data = @unserialize($row->data)) === false) {
			$data = null;
		}

		$entity = $class::create();

		return $entity->fill([
			'name' => $row->name,
			'type' => $row->type,
			'description' => $row->description,
			'ruleName' => $row->rule_name,
			'data' => $data,
			'createdAt' => $row->created_at,
			'updatedAt' => $row->updated_at,
		]);
	}

	/**
	 * @param $row
	 * @return $this
	 */
	protected function populateRule($row)
	{
		if (!isset($row->data) || ($data = @unserialize($row->data)) === false) {
			$data = null;
		}

		$entity = Role::create($this->databaseAdapter);

		return $entity->fill([
			'name' => $row->name,
			'data' => $data,
			'createdAt' => $row->created_at,
			'updatedAt' => $row->updated_at,
		]);
	}

	/**
	 * @return array
	 */
	public function getRules()
	{
		if ($this->rules !== null) {
			return $this->rules;
		}

		$results = $this->databaseAdapter->getRules();

		$this->rules = [];
		foreach ($results as $result) {
			$this->rules[$result->name] = unserialize($result->data);
		}

		return $this->rules;
	}

	/**
	 * @param $roleName
	 * @return array
	 */
	public function getPermissionsByRole($roleName)
	{
		$childrenList = $this->getChildrenList();
		$result = [];

		$this->getChildrenRecursive($roleName, $childrenList, $result);

		if (empty($result)) {
			return [];
		}

		$permissions = $this->databaseAdapter->getItemsByNames(array_keys($result), AbstractItem::TYPE_PERMISSION);

		$results = [];
		foreach ($permissions as $permission) {
			$results[$permission->name] = $this->populateItem($permission);
		}

		return $results;
	}

	/**
	 * Returns the children for every parent.
	 *
	 * @return array the children list. Each array key is a parent item name,
	 * and the corresponding array value is a list of child item names.
	 */
	protected function getChildrenList()
	{
		$parents = $this->databaseAdapter->getAllItemChildren();

		$results = [];
		foreach ($parents as $parent) {
			$results[$parent->parent][] = $parent->child;
		}

		return $results;
	}

	/**
	 * Recursively finds all children and grand children of the specified item.
	 *
	 * @param $name
	 * @param $childrenList
	 * @param $result
	 */
	protected function getChildrenRecursive($name, $childrenList, &$result)
	{
		if (isset($childrenList[$name])) {
			foreach ($childrenList[$name] as $child) {
				$result[$child] = true;
				$this->getChildrenRecursive($child, $childrenList, $result);
			}
		}
	}

	/**
	 * @param $userId
	 * @return array
	 */
	public function getPermissionsByUser($userId)
	{
		if (empty($userId)) {
			return [];
		}

		$assignments = $this->databaseAdapter->getAssignmentsByUser($userId);

		$childrenList = $this->getChildrenList();

		$result = [];

		foreach ($assignments as $assignment) {
			$this->getChildrenRecursive($assignment->item_name, $childrenList, $result);
		}

		if (empty($result)) {
			return [];
		}

		$permissions = $this->databaseAdapter->getItemsByNames(array_keys($result), AbstractItem::TYPE_PERMISSION);

		$results = [];
		foreach ($permissions as $permission) {
			$results[$permission->name] = $this->populateItem($permission);
		}

		return $results;
	}

	/**
	 * @param $roleName
	 * @param $userId
	 * @return $this|null
	 */
	public function getAssignment($roleName, $userId)
	{
		if (empty($userId)) {
			return null;
		}

		$row = $this->databaseAdapter->getAssignmentByUserAndRole($userId, $roleName);

		if ($row === false) {
			return null;
		}

		$assignment = Assignment::create($this->databaseAdapter);

		return $assignment->fill([
			'userId' => $row->user_id,
			'roleName' => $row->item_name,
			'createdAt' => $row->created_at,
		]);
	}

	/**
	 * @param $userId
	 * @return bool
	 */
	public function revokeAll($userId)
	{
		if (empty($userId)) {
			return false;
		}

		return $this->databaseAdapter->deleteAssignmentsForUser($userId) > 0;
	}

	/**
	 * @param $name
	 * @return \Congredi\Rbac\Entities\Abstracts\AbstractItem|null
	 */
	public function getRole($name)
	{
		$item = $this->getItem($name, AbstractItem::TYPE_ROLE);

		return $item instanceof AbstractItem && $item->type == AbstractItem::TYPE_ROLE ? $item : null;
	}

	/**
	 * @param $name
	 * @param $type
	 * @return null
	 */
	protected function getItem($name, $type = null)
	{
		if (empty($name)) {
			return null;
		}

		if (!empty($this->items[$name])) {
			return $this->items[$name];
		}

		$result = $this->databaseAdapter->getItemByName($name, $type);

		if ($result === null) {
			return $result;
		}

		if (!isset($result->data) || ($data = @unserialize($result->data)) === false) {
			$result->data = null;
		}

		return $this->populateItem($result);
	}

	/**
	 * @return mixed
	 */
	public function getRoles()
	{
		return $this->getItems(AbstractItem::TYPE_ROLE);
	}

	/**
	 * @param $type
	 * @return array
	 */
	protected function getItems($type)
	{

		$items = $this->databaseAdapter->getItemsByType($type);

		$result = [];
		foreach ($items as $item) {
			$items[$item->name] = $this->populateItem($item);
		}

		return $result;
	}

	/**
	 * @return mixed
	 */
	public function getPermissions()
	{
		return $this->getItems(AbstractItem::TYPE_PERMISSION);
	}

	/**
	 * @param $name
	 * @return \Congredi\Rbac\Entities\Abstracts\AbstractItem|null
	 */
	public function getPermission($name)
	{
		$item = $this->getItem($name, AbstractItem::TYPE_PERMISSION);

		return $item instanceof AbstractItem && $item->type == AbstractItem::TYPE_PERMISSION ? $item : null;
	}

	/**
	 * @inheritdoc
	 */
	public function checkAccess($userId, $permissionName, $params = [])
	{
		$assignments = $this->getAssignments($userId);
		if ($this->items !== null) {
			return $this->checkAccessFromCache($userId, $permissionName, $params, $assignments);
		}
		else {
			return $this->checkAccessRecursive($userId, $permissionName, $params, $assignments);
		}
	}

	/**
	 * @param $userId
	 * @return array
	 */
	public function getAssignments($userId)
	{
		if (empty($userId)) {
			return [];
		}

		$assignments = $this->databaseAdapter->getAssignmentsByUser($userId);

		$results = [];
		foreach ($assignments as $assignment) {
			$object = Assignment::create($this->databaseAdapter);
			$results[$assignment->item_name] = $object->fill([
				'userId' => $assignment->user_id,
				'roleName' => $assignment->item_name,
				'createdAt' => $assignment->created_at,
			]);
		}

		return $results;
	}

	/**
	 * Performs access check for the specified user based on the data loaded from cache.
	 *
	 * @param $user
	 * @param $itemName
	 * @param $params
	 * @param $assignments
	 * @return bool
	 */
	protected function checkAccessFromCache($user, $itemName, $params, $assignments)
	{
		if (!isset($this->items[$itemName])) {
			return false;
		}

		$item = $this->items[$itemName];

		if (!$this->executeRule($user, $item, $params)) {
			return false;
		}

		if (isset($assignments[$itemName])) {
			return true;
		}

		if (!empty($this->parents[$itemName])) {
			foreach ($this->parents[$itemName] as $parent) {
				if ($this->checkAccessFromCache($user, $parent, $params, $assignments)) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Executes the rule associated with the specified auth item.
	 *
	 * @param $user
	 * @param $item
	 * @param $params
	 * @return bool
	 * @throws \Congredi\Rbac\Exceptions\InvalidConfigException
	 */
	protected function executeRule($user, $item, $params)
	{
		if ($item->ruleName === null) {
			return true;
		}

		$rule = $this->getRule($item->ruleName);

		if ($rule instanceof RuleInterface) {
			return $rule->execute($user, $item, $params);
		}
		else {
			throw new InvalidConfigException("Rule not found: {$item->ruleName}");
		}
	}

	/**
	 * @param $name
	 * @return mixed|null
	 */
	public function getRule($name)
	{
		if ($this->rules !== null) {
			return isset($this->rules[$name]) ? $this->rules[$name] : null;
		}

		$row = $this->databaseAdapter->getRuleByName($name);

		return $row === false ? null : unserialize($row->data);
	}

	/**
	 * Performs access check for the specified user.
	 *
	 * @param $user
	 * @param $itemName
	 * @param $params
	 * @param $assignments
	 * @return bool
	 */
	protected function checkAccessRecursive($user, $itemName, $params, $assignments)
	{
		if (($item = $this->getItem($itemName)) === null) {
			return false;
		}

		if (!$this->executeRule($user, $item, $params)) {
			return false;
		}

		if (isset($assignments[$itemName])) {
			return true;
		}

		$parents = $this->databaseAdapter->getParentsForChild($itemName);

		foreach ($parents as $parent) {
			if ($this->checkAccessRecursive($user, $parent, $params, $assignments)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function removeAll()
	{
		$this->removeAllAssignments();
		$this->databaseAdapter->deleteAllParentChildAssoc();
		$this->databaseAdapter->deleteAllItems();
		$this->databaseAdapter->deleteAllRules();
	}

	/**
	 * @inheritdoc
	 */
	public function removeAllAssignments()
	{
		$this->databaseAdapter->deleteAllAssignments();
	}

	/**
	 * @inheritdoc
	 */
	public function removeAllPermissions()
	{
		$this->removeAllItems(AbstractItem::TYPE_PERMISSION);
	}

	/**
	 * Removes all auth items of the specified type.
	 *
	 * @param integer $type the auth item type (either Item::TYPE_PERMISSION or Item::TYPE_ROLE)
	 */
	protected function removeAllItems($type)
	{
		$this->databaseAdapter->removeAllItems($type);
	}

	/**
	 * @inheritdoc
	 */
	public function removeAllRoles()
	{
		$this->removeAllItems(AbstractItem::TYPE_ROLE);
	}

	/**
	 * @inheritdoc
	 */
	public function removeAllRules()
	{
		$this->databaseAdapter->deleteAllRules();
	}
}
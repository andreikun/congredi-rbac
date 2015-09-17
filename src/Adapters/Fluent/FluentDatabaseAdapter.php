<?php namespace Congredi\Rbac\Fluent;

use Congredi\Rbac\AbstractDatabaseAdapter;
use Congredi\Rbac\Entities\Abstracts\AbstractItem;
use Congredi\Rbac\Entities\Abstracts\AbstractRole;
use Congredi\Rbac\Entities\Rule;
use Illuminate\Database\ConnectionResolverInterface;

class FluentDatabaseAdapter extends AbstractDatabaseAdapter
{
	/**
	 * @param \Illuminate\Database\ConnectionResolverInterface $resolver
	 * @param $configs
	 */
	public function __construct(ConnectionResolverInterface $resolver, $configs)
	{
		$this->resolver = $resolver;
		$this->connectionName = null;

		if (isset($configs['item_table'])) {
			$this->itemTable = $configs['item_table'];
		}

		if (isset($configs['assignment_table'])) {
			$this->assignmentTable = $configs['assignment_table'];
		}

		if (isset($configs['rule_table'])) {
			$this->ruleTable = $configs['rule_table'];
		}

		if (isset($configs['item_child_table'])) {
			$this->itemChildTable = $configs['item_child_table'];
		}
	}

	/**
	 * @return mixed
	 */
	public function getDriverName()
	{
		return $this->getConnection()->getDriverName();
	}

	/**
	 * Get the connection.
	 *
	 * @return \Illuminate\Database\ConnectionInterface
	 */
	public function getConnection()
	{
		return $this->resolver->connection($this->connectionName);
	}

	/**
	 * @param $userId
	 * @param $type
	 * @return array|static[]
	 */
	public function getItemsByUser($userId, $type = null)
	{
		$query = $this->getConnection()->table($this->assignmentTable)
			->join($this->itemTable, "{$this->assignmentTable}.item_name", '=', "{$this->itemTable}.name")
			->where("{$this->assignmentTable}.user_id", (string) $userId);

		if (null !== $type) {
			$query->where("{$this->itemTable}.type", $type);
		}

		return $query->get();
	}

	/**
	 * @return array|static[]
	 */
	public function getRules()
	{
		return $this->getConnection()->table($this->ruleTable)->get();
	}

	/**
	 * @param array $names
	 * @param null $type
	 */
	public function getItemsByNames($names = [], $type = null)
	{
		$query = $this->getConnection()->table($this->itemTable)
			->whereIn('name', $names);

		if (null !== $type) {
			$query->where('type', $type);
		}

		return $query->get();
	}

	/**
	 * @param $userId
	 * @return array|static[]
	 */
	public function getAssignmentsByUser($userId)
	{
		return $this->getConnection()->table($this->assignmentTable)
			->where('user_id', (string) $userId)
			->get();
	}

	/**
	 * @return array|static[]
	 */
	public function getAllItemChildren()
	{
		return $this->getConnection()->table($this->itemChildTable)->get();
	}

	/**
	 * @param $userId
	 * @param $roleName
	 * @return mixed|static
	 */
	public function getAssignmentByUserAndRole($userId, $roleName)
	{
		return $this->getConnection()->table($this->assignmentTable)
			->where('user_id', (string) $userId)
			->where('item_name', $roleName)
			->first();
	}

	/**
	 * @param $userId
	 * @return int
	 */
	public function deleteAssignmentsForUser($userId)
	{
		return $this->getConnection()->table($this->assignmentTable)
			->where('user_id', (string) $userId)
			->delete();
	}

	/**
	 * @param $name
	 * @param null $type
	 * @return mixed|static
	 */
	public function getItemByName($name, $type = null)
	{
		$query = $this->getConnection()->table($this->itemTable)
			->where('name', $name);

		if (null !== $type) {
			$query->where('type', $type);
		}

		return $query->first();
	}

	/**
	 * @param $type
	 * @return array|static[]
	 */
	public function getItemsByType($type)
	{
		return $this->getConnection()->table($this->itemTable)
			->where('type', $type)->get();
	}

	/**
	 * @param $name
	 * @return mixed|static
	 */
	public function getRuleByName($name)
	{
		return $this->getConnection()->table($this->ruleTable)
			->where('name', $name)
			->first();
	}

	/**
	 * @param $itemName
	 * @return array
	 */
	public function getParentsForChild($itemName)
	{
		return $this->getConnection()->table($this->itemChildTable)
			->where('child', $itemName)
			->lists('parent');
	}

	/**
	 * @param \Congredi\Rbac\Entities\Abstracts\AbstractItem $item
	 * @return bool
	 */
	public function addItem(AbstractItem $item)
	{
		$time = date('Y-m-d H:i:s', time());

		if ($item->createdAt === null) {
			$item->createdAt = $time;
		}

		if ($item->updatedAt === null) {
			$item->updatedAt = $time;
		}

		$this->getConnection()->table($this->itemTable)->insert([
			'name' => $item->name,
			'type' => $item->type,
			'description' => $item->description,
			'rule_name' => $item->ruleName,
			'data' => $item->data === null ? null : serialize($item->data),
			'created_at' => $item->createdAt,
			'updated_at' => $item->updatedAt,
		]);

		return true;
	}

	/**
	 * @param \Congredi\Rbac\Entities\Abstracts\AbstractItem $item
	 * @return bool
	 */
	public function removeItem(AbstractItem $item)
	{
		if (!$this->supportsCascadeUpdate()) {
			$this->getConnection()->table($this->itemChildTable)
				->where('parent', $item->name)
				->orWhere('child', $item->name)
				->delete();
			$this->getConnection()->table($this->assignmentTable)
				->where('item_name', $item->name)
				->delete();
		}

		$this->getConnection()->table($this->itemTable)
			->where('name', $item->name)
			->delete();

		return true;
	}

	/**
	 * @param \Congredi\Rbac\Entities\Abstracts\AbstractItem $item
	 * @param $name
	 * @return bool
	 */
	public function updateItem(AbstractItem $item, $name)
	{
		if ($item->name !== $name && !$this->supportsCascadeUpdate()) {
			$this->getConnection()->table($this->itemChildTable)
				->where('parent', $item->name)
				->update(['parent' => $name]);
			$this->getConnection()->table($this->itemChildTable)
				->where('child', $item->name)
				->update(['child' => $name]);
			$this->getConnection()->table($this->assignmentTable)
				->where('item_name', $item->name)
				->update(['item_name' => $name]);
		}

		$item->updatedAt = date('Y-m-d H:i:s', time());

		$this->getConnection()->table($this->itemTable)
			->where('name', $name)
			->update([
				'name' => $item->name,
				'description' => $item->description,
				'rule_name' => $item->ruleName,
				'data' => $item->data === null ? null : serialize($item->data),
				'updated_at' => $item->updatedAt,
			]);

		return true;
	}

	/**
	 * @param \Congredi\Rbac\Entities\Rule $rule
	 * @return bool
	 */
	public function addRule(Rule $rule)
	{
		$time = date('Y-m-d H:i:s', time());

		if ($rule->createdAt === null) {
			$rule->createdAt = $time;
		}

		if ($rule->updatedAt === null) {
			$rule->updatedAt = $time;
		}

		$this->getConnection()->table($this->ruleTable)->insert([
			'name' => $rule->name,
			'data' => serialize($rule),
			'created_at' => $rule->createdAt,
			'updated_at' => $rule->updatedAt,
		]);

		return true;
	}

	/**
	 * @param \Congredi\Rbac\Entities\Rule $rule
	 * @param $name
	 * @return bool
	 */
	public function updateRule(Rule $rule, $name)
	{
		if ($rule->name !== $name && !$this->supportsCascadeUpdate()) {
			$this->getConnection()->table($this->itemTable)
				->where('rule_name', $name)
				->update(['rule_name' => $rule->name]);
		}

		$rule->updatedAt = date('Y-m-d H:i:s', time());

		$this->getConnection()->table($this->ruleTable)
			->where('name', $name)
			->update([
				'name' => $rule->name,
				'data' => serialize($rule),
				'updated_at' => $rule->updatedAt,
			]);

		return true;
	}

	/**
	 * @param \Congredi\Rbac\Entities\Rule $rule
	 * @return bool
	 */
	public function removeRule(Rule $rule)
	{
		if (!$this->supportsCascadeUpdate()) {
			$this->getConnection()->table($this->itemTable)
				->where('rule_name', $rule->name)
				->update(['rule_name' => null]);
		}

		$this->getConnection()->table($this->ruleTable)
			->where('name', $rule->name)
			->delete();

		return true;
	}

	/**
	 * @param $parentItem
	 * @param $childItem
	 * @return bool
	 */
	public function addChild($parentItem, $childItem)
	{
		$this->getConnection()->table($this->itemChildTable)->insert([
			'parent' => $parentItem->name,
			'child' => $childItem->name
		]);

		return true;
	}

	/**
	 * @param $parentItem
	 * @param $childItem
	 * @return int
	 */
	public function removeChild($parentItem, $childItem)
	{
		return $this->getConnection()->table($this->itemChildTable)
			->where('parent', $parentItem->name)
			->where('child', $childItem->name)
			->delete();
	}

	/**
	 * @param $parentItem
	 * @return int
	 */
	public function removeChildren($parentItem)
	{
		return $this->getConnection()->table($this->itemChildTable)
			->where('parent', $parentItem->name)
			->delete();
	}

	/**
	 * @param $name
	 * @return array|static[]
	 */
	public function getChildrenByName($name)
	{
		return $this->getConnection()->table($this->itemTable)
			->join($this->itemChildTable, "{$this->itemTable}.name", '=', "{$this->itemChildTable}.child")
			->where("{$this->itemChildTable}.parent", $name)
			->select("{$this->itemTable}.*")->get();
	}

	/**
	 * @param $parent
	 * @param $child
	 * @return array|static[]
	 */
	public function getItemChild($parent, $child)
	{
		return $this->getConnection()->table($this->itemChildTable)
			->where('parent', $parent->name)
			->where('child', $child->name)
			->get();
	}

	/**
	 * @param \Congredi\Rbac\Entities\Abstracts\AbstractRole $role
	 * @param $userId
	 * @return bool
	 */
	public function addAssignment(AbstractRole $role, $userId)
	{
		$this->getConnection()->table($this->assignmentTable)->insert([
			'user_id' => $userId,
			'item_name' => $role->name,
			'created_at' => date('Y-m-d H:i:s', time())
		]);

		return true;
	}

	/**
	 * @param \Congredi\Rbac\Entities\Abstracts\AbstractRole $role
	 * @param $userId
	 * @return int
	 */
	public function removeAssignment(AbstractRole $role, $userId)
	{
		return $this->getConnection()->table($this->assignmentTable)
			->where('user_id', (string) $userId)
			->where('item_name', $role->name)
			->delete();
	}

	/**
	 * @return bool
	 */
	public function deleteAllRules()
	{
		if (!$this->supportsCascadeUpdate()) {
			$this->getConnection()->table($this->itemTable)
				->update(['ruleName' => null]);
		}

		$this->getConnection()->table($this->ruleTable)->delete();

		return true;
	}

	/**
	 * @return bool
	 */
	public function deleteAllItems()
	{
		$this->getConnection()->table($this->itemTable)->delete();

		return true;
	}

	/**
	 * @return bool
	 */
	public function deleteAllAssignments()
	{
		$this->getConnection()->table($this->assignmentTable)->delete();

		return true;
	}

	/**
	 * @return bool
	 */
	public function deleteAllParentChildAssoc()
	{
		$this->getConnection()->table($this->itemChildTable)->delete();

		return true;
	}

	/**
	 * @param $type
	 * @return bool|void
	 */
	public function removeAllItems($type)
	{
		if (!$this->supportsCascadeUpdate()) {
			$this->getConnection()->table($this->itemTable)
				->where('type', $type)
				->lists('name');

			if (empty($names)) {
				return true;
			}

			$key = $type == AbstractItem::TYPE_PERMISSION ? 'child' : 'parent';
			$this->getConnection()->table($this->itemChildTable)
				->whereIn($key, $names)
				->delete();
			$this->getConnection()->table($this->assignmentTable)
				->whereIn('item_name', $names)
				->delete();
		}

		$this->getConnection()->table($this->itemTable)
			->where('type', $type)
			->delete();

		return true;
	}
}
<?php namespace Congredi\Rbac\Entities\Interfaces;


interface RoleInterface extends ItemInterface
{
	/**
	 * @param $userId
	 * @return mixed
	 */
	public function assignTo($userId);

	/**
	 * @param $userId
	 * @return mixed
	 */
	public function revokeFrom($userId);

	/**
	 * @param \Congredi\Rbac\Entities\Interfaces\ItemInterface $item
	 * @return mixed
	 */
	public function addChild(ItemInterface $item);

	/**
	 * @param \Congredi\Rbac\Entities\Interfaces\ItemInterface $item
	 * @return mixed
	 */
	public function removeChild(ItemInterface $item);

	/**
	 * @return mixed
	 */
	public function removeChildren();
}
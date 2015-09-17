<?php namespace Congredi\Rbac\Entities\Interfaces;


interface BaseInterface
{
	/**
	 * @return mixed
	 */
	public function save();

	/**
	 * @return mixed
	 */
	public function remove();

	/**
	 * @param $data
	 * @return mixed
	 */
	public function update($data);
}
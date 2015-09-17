<?php namespace Congredi\Rbac\Entities\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface RuleInterface extends BaseInterface
{
	public function execute(Model $user, ItemInterface $item, $params);
}
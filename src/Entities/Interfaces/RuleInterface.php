<?php namespace Congredi\Rbac\Entities\Interfaces;

interface RuleInterface extends BaseInterface
{
	public function execute($user, ItemInterface $item, $params);
}
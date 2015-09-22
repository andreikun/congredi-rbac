<?php namespace Congredi\Rbac\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * This is the rbac facade class.
 */
class Rbac extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'rbac.manager';
	}
}
<?php defined('ZAP_ROOT') OR die('No direct script access.');
/**
 * Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

ZAP_Autoloader::register();

/**
 * ZAP_Autoloader class.
 *
 * @package   ZAP
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
abstract class ZAP_Autoloader
{
	/**
	 * Internal autoloader for spl_autoload_register().
	 * 
	 * @param  string $class
	 * @return void
	 */
   	public static function autoload($class)
	{
		if (0 !== strpos($class, 'ZAP'))
		{
			return FALSE;
		}
		$path = ZAP_ROOT.str_replace('_', DIRECTORY_SEPARATOR, $class).'.php';
		if (!file_exists($path))
		{
			return FALSE;
		}
		require_once $path;
	}

	/**
	 * Configure autoloading using Zimbra API PHP.
	 *
	 * @return void
	 */
	public static function register()
	{
		spl_autoload_register(array('ZAP_Autoloader', 'autoload'));
	}

	/**
	 * Unregister autoloading using Zimbra API PHP.
	 *
	 * @return void
	 */
	public static function unregister()
	{
		spl_autoload_unregister(array('ZAP_Autoloader', 'autoload'));
	}
}
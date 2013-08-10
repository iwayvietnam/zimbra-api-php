<?php
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

if (!defined('ZAP_ROOT'))
{
	define('ZAP_ROOT', dirname(__FILE__) . '/');
	require(ZAP_ROOT . 'ZAP/Autoloader.php');
}

/**
 * General utility class in Zimbra API PHP, not to be instantiated.
 * 
 * @package ZAP
 * 
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
abstract class ZAP
{
	/**
	 * @var array Settings
	 */
	private static $_settings = array();

	/**
	 * Configure setting values.
	 * This method defines settings and acts as a setter and a getter.
	 * 
	 * @param  string|array $name  If a string, the name of the setting to set or retrieve. Else an associated array of setting names and values
	 * @param  mixed        $value If name is a string, the value of the setting identified by $name
	 * @return mixed        The value of a setting if only one argument is a string
	 */
	public static function setting($name, $value = NULL)
	{
		if(NULL === $value)
		{
			if (is_array($name))
			{
				self::$_settings = array_merge(self::$_settings, $name);
			}
			else
			{
				return isset(self::$_settings[$name]) ? self::$_settings[$name] : NULL;
			}
		}
		else
		{
			self::$_settings[$name] = $value;
		}
	}

	/**
	 * Creates an instance of a ZAP_Account_Interface base on parameters.
	 *
	 * @param  string  $config Configuration name from ZAP::setting
	 * @return ZAP_Account_Interface
	 */
	public static function account($config = 'default')
	{
		if(!isset(self::$_settings[$config]['driver']) OR !isset(self::$_settings[$config]['location']))
		{
			throw new ZAP_Exception("You must set driver or location setting value");
		}
		return ZAP_API_Account::instance($config);
	}

	/**
	 * Creates an instance of a ZAP_API_Admin_Interface base on parameters.
	 *
	 * @param  string  $config Configuration name from ZAP::setting
	 * @return ZAP_API_Admin_Interface
	 */
	public static function admin($config = 'default')
	{
		if(!isset(self::$_settings[$config]['driver']) OR !isset(self::$_settings[$config]['location']))
		{
			throw new ZAP_Exception("You must set driver or location setting value");
		}
		return ZAP_API_Admin::instance($config);
	}
}
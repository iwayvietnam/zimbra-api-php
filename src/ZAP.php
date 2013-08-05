<?php
/*
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
	private static $_settings = array();
  	/**
   	 * Internal autoloader for spl_autoload_register().
   	 * 
   	 * @param string $class
   	 */
   	public static function autoload($class)
	{
		if (0 !== strpos($class, 'ZAP'))
		{
			return false;
		}
		$path = dirname(__FILE__).DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $class).'.php';
		if (!file_exists($path))
		{
			return false;
		}
		require_once $path;
	}

  	/**
   	 * Configure autoloading using Zimbra API PHP.
   	 * 
   	 * This is designed to play nicely with other autoloaders.
   	 */
	public static function registerAutoload()
	{
		spl_autoload_register(array('ZAP', 'autoload'));
	}

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
}
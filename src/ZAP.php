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
	 * @var string Unique identifier
	 */
	private static $_uniqid = NULL;

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
	 * @param  string $driver   Driver
	 * @param  string $location The Zimbra api soap location.
	 * @return ZAP_Account_Interface
	 */
	public static function account($driver = NULL, $location = NULL)
	{
		self::_init();
		if($driver !== NULL)
		{
			self::$_settings[self::$_uniqid]['account']['driver'] = $driver;
		}
		else
		{
			$driver = self::$_settings[self::$_uniqid]['account']['driver'];
		}
		if($location !== NULL)
		{
			self::$_settings[self::$_uniqid]['account']['location'] = $location;
		}
		else
		{
			$location = self::$_settings[self::$_uniqid]['account']['location'];
		}
		return ZAP_API_Account::instance($driver, $location);
	}

	/**
	 * Creates an instance of a ZAP_API_Admin_Interface base on parameters.
	 *
	 * @param  string $driver   Driver
	 * @param  string $location The Zimbra api soap location.
	 * @return ZAP_API_Admin_Interface
	 */
	public static function admin($driver = NULL, $location = NULL)
	{
		if($driver !== NULL)
		{
			self::$_settings[self::$_uniqid]['admin']['driver'] = $driver;
		}
		else
		{
			$driver = self::$_settings[self::$_uniqid]['admin']['driver'];
		}
		if($location !== NULL)
		{
			self::$_settings[self::$_uniqid]['admin']['location'] = $location;
		}
		else
		{
			$location = self::$_settings[self::$_uniqid]['admin']['location'];
		}
		return ZAP_API_Admin::instance($driver, $location);
	}

	/**
	 * Creates an instance of a ZAP_API_Mail_Interface base on parameters.
	 *
	 * @param  string $driver   Driver
	 * @param  string $location The Zimbra api soap location.
	 * @return ZAP_API_Mail_Interface
	 */
	public static function mail($driver = NULL, $location = NULL)
	{
		if($driver !== NULL)
		{
			self::$_settings[self::$_uniqid]['admin']['driver'] = $driver;
		}
		else
		{
			$driver = self::$_settings[self::$_uniqid]['admin']['driver'];
		}
		if($location !== NULL)
		{
			self::$_settings[self::$_uniqid]['admin']['location'] = $location;
		}
		else
		{
			$location = self::$_settings[self::$_uniqid]['admin']['location'];
		}
	}

	/**
	 * Initialize default parameters.
	 *
	 * @return void
	 */
	private static function _init()
	{
		if(empty(self::$_uniqid))
		{
			self::$_uniqid = uniqid();
			self::setting(array(
				self::$_uniqid => array(
					'account' => array(
						'driver' => 'soap',
						'location' => 'https://localhost/service/soap',
					),
					'admin' => array(
						'driver' => 'soap',
						'location' => 'https://localhost:7071/service/admin/soap',
					),
					'mail' => array(
						'driver' => 'soap',
						'location' => 'https://localhost/service/soap',
					),
				),
			));
		}
	}
}
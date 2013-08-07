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

/**
 * ZAP_API_Account is a class which allows to manage Zimbra
 * @package   ZAP
 * @category  API
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
abstract class ZAP_API_Account extends ZAP_API_Base implements ZAP_API_Account_Interface
{
	/**
	 * @var ZAP_Account_Interface
	 */
	private static $_instances = array();

	/**
	 * Creates a singleton of a ZAP_Account_Interface base on parameters.
	 *
	 * @param  string  $config Configuration name from ZAP::setting
	 * @return ZAP_Account_Interface
	 */
	public static function instance($config = 'default')
	{
		$config = !empty($config) ? $config : 'default';
		$setting = ZAP::setting($config);
		$driver = isset($setting['driver']) ? $setting['driver'] : 'soap';
		$server = isset($setting['server']) ? $setting['server'] : 'localhost';
		$port = isset($setting['port']) ? (int) $setting['port'] : 443;
		$ssl = isset($setting['ssl']) ? (bool) $setting['port'] : TRUE;

		$key = md5($driver.$server.$port.($ssl ? 'true' : 'false'));
		if (isset(self::$_instances[$key]) AND (self::$_instances[$key] instanceof ZAP_API_Account_Interface))
		{
			return self::$_instances[$key];
		}
		else
		{
			self::$_instances[$key] = self::factory($driver, $server, $port, $ssl);
			return self::$_instances[$key];			
		}
	}

	/**
	 * Returns a new ZAP_Account_Interface object.
	 *
	 * @param  string  $driver Driver
	 * @param  string  $server Server address
	 * @param  integer $port   Server port
	 * @param  bool    $ssl    Ssl
	 * @return ZAP_Account_Interface
	 */
	public static function factory($driver = 'soap', $server = 'localhost', $port = 443, $ssl = TRUE)
	{
		switch (strtolower($driver))
		{
			case 'curl':
				return new ZAP_API_Account_CURL($server, $port, $ssl);
			case 'http':
				return new ZAP_API_Account_HTTP($server, $port, $ssl);
			case 'socket':
				return new ZAP_API_Account_Socket($server, $port, $ssl);
			case 'wsdl':
				return new ZAP_API_Account_WSDL($server, $port, $ssl);
			default:
				return new ZAP_API_Account_Soap($server, $port, $ssl);
		}
	}
}

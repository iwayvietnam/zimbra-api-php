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

/**
 * ZAP_API_Mail is a class which allows to manage mail in Zimbra
 * @package   ZAP
 * @category  API
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
abstract class ZAP_API_Mail extends ZAP_API_Base implements ZAP_API_Mail_Interface
{
	/**
	 * @var ZAP_Mail_Interface
	 */
	private static $_instances = array();

	/**
	 * Creates a singleton of a ZAP_Mail_Interface base on parameters.
	 *
	 * @param  string $driver   Driver
	 * @param  string $location The Zimbra api soap location.
	 * @return ZAP_Mail_Interface
	 */
	public static function instance($driver = 'soap', $location = 'https://localhost/service/soap')
	{
		$key = md5($driver.$location);
		if (isset(self::$_instances[$key]) AND (self::$_instances[$key] instanceof ZAP_API_Mail_Interface))
		{
			return self::$_instances[$key];
		}
		else
		{
			self::$_instances[$key] = self::factory($driver, $location);
			return self::$_instances[$key];			
		}
	}

	/**
	 * Returns a new ZAP_Mail_Interface object.
	 *
	 * @param  string $driver   Driver
	 * @param  string $location The Zimbra api soap location.
	 * @return ZAP_Mail_Interface
	 */
	public static function factory($driver = 'soap', $location = 'https://localhost/service/soap')
	{
		switch (strtolower($driver))
		{
			case 'curl':
				return new ZAP_API_Mail_CURL($location);
			case 'http':
				return new ZAP_API_Mail_HTTP($location);
			case 'socket':
				return new ZAP_API_Mail_Socket($location);
			case 'wsdl':
				return new ZAP_API_Mail_WSDL($location);
			default:
				return new ZAP_API_Mail_Soap($location);
		}
	}
}
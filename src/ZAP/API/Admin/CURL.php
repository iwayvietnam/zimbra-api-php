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
 * ZAP_API_Admin_CURL is a class which allows to connect Zimbra API administration functions via SOAP using URL
 * @package   ZAP
 * @category  Admin
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
class ZAP_API_Admin_CURL extends ZAP_API_Admin_Base
{
	/**
	 * ZAP_Admin_CURL constructor.
	 *
	 * @param string $server   The server name.
	 * @param string $account  The user account.
	 * @param string $password The user password.
	 * @param bool   $ssl.
	 */
	public function __construct($server, $port = 7071, $ssl = TRUE)
	{
		parent::__construct($server, $port, $ssl);
		$this->_client = new ZAP_Client_CURL($this->_location, $this->_namespace);
	}
}

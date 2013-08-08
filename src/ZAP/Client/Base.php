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
 * ZAP_Client_Base is a class which provides a client for SOAP servers
 * @package   ZAP
 * @category  Client
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
abstract class ZAP_Client_Base implements ZAP_Client_Interface
{
	/**
	 * @var string Authentication token
	 */
	protected $_authToken;

	/**
	 * @var string Authentication identify
	 */
	protected $_sessionId;

	/**
	 * @var string Soap namespace
	 */
	protected $_namespace = 'urn:zimbra';

	/**
	 * @var ZAP_Soap_Message
	 */
	protected $_soapMessage;

	/**
	 * @var string Server location
	 */
	protected $_location;

	/**
	 * ZAP_Client_Base constructor
	 *
	 * @param string $location  The URL to request.
	 * @param string $namespace The SOAP namespace.
	 */
	public function __construct($location, $namespace = 'urn:zimbra'){
		$this->_location = $location;
		$this->_namespace = !empty($namespace) ? $namespace : 'urn:zimbra';
		$this->_soapMessage = new ZAP_Soap_Message($this->_namespace);
	}

	/**
	 * Set or get authentication token.
	 *
	 * @param  string $authToken Authentication token
	 * @return mix
	 */
	public function authToken($authToken = NULL)
	{
		if($authToken === NULL)
		{
			return $this->_authToken;
		}
		$this->_authToken = (string) $authToken;
		$this->_soapMessage->addHeader('authToken', $this->_authToken);
		return $this;
	}

	/**
	 * Set or get authentication session identify.
	 *
	 * @param  string $sessionId Authentication session identify
	 * @return mix
	 */
	public function sessionId($sessionId = NULL)
	{
		if($sessionId === NULL)
		{
			return $this->_sessionId;
		}
		$this->_sessionId = (string) $sessionId;
		$this->_soapMessage->addHeader('sessionId', $this->_sessionId);
		return $this;
	}
}

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
	 * @var string Last response message
	 */
	protected $_response;

	/**
	 * @var array Request headers
	 */
	protected $_headers = array();

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

		$this->_headers = array(
			'Content-Type' => 'text/xml; charset=utf-8',
			'Method'       => 'POST',
			'User-Agent'   => $_SERVER['HTTP_USER_AGENT'],
		);
	}

	/**
	 * Method overloading.
	 *
	 * @param  string $name Method name
	 * @param  array  $args Method arguments
	 * @return mix
	 */
	public function __call($name, array $args)
	{
		$params = $attrs = array();
		if(isset($args[0]))
		{
			$params = is_array($args[0]) ? $args[0] : array($args[0]);
		}
		if(isset($args[1]))
		{
			$attrs = is_array($args[1]) ? $args[1] : array($args[1]);
		}

		$result = $this->soapRequest(ucfirst($name).'Request', $params, $attrs);
		$response = ucfirst($name).'Response';
		return $result->$response;
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

	/**
	 * Returns last SOAP request.
	 *
	 * @return mix The last SOAP request, as an XML string.
	 */
	public function lastRequest()
	{
		return (string) $this->_soapMessage;
	}

	/**
	 * Returns last SOAP response.
	 *
	 * @return mix The last SOAP response, as an XML string.
	 */
	public function lastResponse()
	{
		return $this->_response;
	}
}

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
 * The SoapClient class provides a client for SOAP 1.1, SOAP 1.2 servers.
 * It can be used in WSDL or non-WSDL mode.
 * @package   ZAP
 * @category  Client
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
abstract class ZAP_Client_Soap_Base extends SoapClient implements ZAP_Client_Interface
{
	/**
	 * @var string Soap namespace
	 */
	protected $_namespace = 'urn:zimbra';

	/**
	 * @var array Authentication headers
	 */
	private $_headers = array();

	/**
	 * @var array filter callbacks
	 */
	private $_filters = array();

	/**
	 * ZAP_Client_Soap_Base constructor
	 *
	 * @param string $location  The URL to request.
	 * @param string $namespace The SOAP namespace.
	 */
	public function __construct($location, $namespace = 'urn:zimbra', $wsdl = FALSE)
	{
		$this->_namespace = $namespace;
		$options = array(
			'trace' => 1,
			'exceptions' => 1,
			'soap_version' => SOAP_1_2,
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
		);
		if($wsdl)
		{
			$options += array(
				'cache_wsdl' => WSDL_CACHE_DISK,
			);
			parent::__construct($location, $options);
		}
		else
		{
			$options += array(
				'location' => $location,
				'uri' => $this->_namespace,
				'style' => SOAP_RPC,
				'use' => SOAP_LITERAL,
			);
			parent::__construct(NULL, $options);
		}
	}

	/**
	 * Performs SOAP request over HTTP.
	 * This method can be overridden in subclasses to implement different transport layers, perform additional XML processing or other purpose.
	 *
	 * @param  string $request  The XML SOAP request.
	 * @param  string $location The URL to request.
	 * @param  string $action   The SOAP action.
	 * @param  int    $version  The SOAP version.
	 * @param  int    $one_way  If one_way is set to 1, this method returns nothing. Use this where a response is not expected.
	 * @return mixed
	 */
	public function __doRequest($request, $location, $action, $version, $one_way = 0)
	{
		$request = $this->_filterRequest($request);
		if ($this->_filters)
		{
			foreach ($this->_filters as $callback)
			{
				$request = call_user_func($callback, $request, $location, $action, $version, $one_way);
			}
		}

		$this->__last_request = $request;
		return parent::__doRequest($request, $location, $action, $version, $one_way);
	}

	public function soapHeader()
	{
		$soapHeader = NULL;
		if(count($this->_headers))
		{
			$soapVar = new SoapVar((object) $this->_headers, SOAP_ENC_OBJECT);
			$soapHeader = new SoapHeader('urn:zimbra', 'context', $soapVar);
		}
		return $soapHeader;
	}

	/**
	 * Filters to be run before request are sent.
	 *
	 * @param  array $callback Callback string, array, or closure
	 * @throws ZAP_Exception
	 * @return ZAP_Client_Soap
	 */
	public function addFilter($callback)
	{
		if(!is_callable($callback))
		{
			throw new ZAP_Exception('Invalid filter specified');
		}

		$this->_filters[] = $callback;

		return $this;
	}

	/**
	 * Set or get authentication token.
	 *
	 * @param  string $authToken Authentication token
	 * @return ZAP_Client_Soap
	 */
	public function authToken($authToken = NULL)
	{
		if($authToken === NULL)
		{
			return isset($this->_headers['authToken']) ? $this->_headers['authToken'] : NULL;
		}
		$this->_headers['authToken'] = (string) $authToken;
		return $this;
	}

	/**
	 * Set or get authentication session identify.
	 *
	 * @param  string $sessionId Authentication session identify
	 * @return ZAP_Client_Soap
	 */
	public function sessionId($sessionId = NULL)
	{
		if($sessionId === NULL)
		{
			return isset($this->_headers['sessionId']) ? $this->_headers['sessionId'] : NULL;
		}
		$this->_headers['sessionId'] = (string) $sessionId;
		return $this;
	}

	/**
	 * Returns last SOAP request.
	 *
	 * @return string The last SOAP request, as an XML string.
	 */
	public function lastRequest()
	{
		return $this->__getLastRequest();
	}

	/**
	 * Returns the SOAP headers from the last request.
	 *
	 * @return array The last SOAP request headers.
	 */
	public function lastRequestHeaders()
	{
		return ZAP_Helpers::extractHeaders($this->__getLastRequestHeaders());
	}

	/**
	 * Returns last SOAP response.
	 *
	 * @return string The last SOAP response, as an XML string.
	 */
	public function lastResponse()
	{
		return $this->__getLastResponse();
	}

	/**
	 * Returns the SOAP headers from the last response.
	 *
	 * @return array The last SOAP response headers.
	 */
	public function lastResponseHeaders()
	{
		return ZAP_Helpers::extractHeaders($this->__getLastResponseHeaders());
	}

	/**
	 * Filter soap request.
	 *
	 * @param  string $request The XML SOAP request.
	 * @return string The XML SOAP request.
	 */
	protected function _filterRequest($request)
	{
		return $request;
	}
}
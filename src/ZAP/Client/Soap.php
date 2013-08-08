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
 * The SoapClient class provides a client for SOAP 1.1, SOAP 1.2 servers.
 * It can be used in WSDL or non-WSDL mode.
 * @package   ZAP
 * @category  Client
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
class ZAP_Client_Soap extends SoapClient implements ZAP_Client_IClient
{
	/**
	 * @var SoapHeader
	 */
	protected $_soapHeader;

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
	 * @var array filter callbacks
	 */
	protected $_filters = array();

	/**
	 * @var array soap function attributes
	 */
	protected $_soapAttributes = array();

	/**
	 * ZAP_Client_Soap constructor
	 *
	 * @param string $location  The URL to request.
	 * @param string $namespace The SOAP namespace.
	 */
	public function __construct($location, $namespace = 'urn:zimbra', $wsdl = FALSE)
	{
		$this->_namespace = $namespace;
		if($wsdl)
		{
			$options = array(
				'trace' => 1,
				'exceptions' => 1,
				'soap_version' => SOAP_1_2,
				'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			);
			parent::__construct($location, $options);
		}
		else
		{
			$options = array(
				'location' => $location,
				'uri' => $this->_namespace,
				'trace' => 1,
				'exceptions' => 1,
				'soap_version' => SOAP_1_2,
				'style' => SOAP_RPC,
				'use' => SOAP_LITERAL,
				'user_agent' => $_SERVER['HTTP_USER_AGENT'],
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
		$headers = array();
		if(!empty($this->_authToken))
		{
			$headers['authToken'] = $this->_authToken;
		}
		if(!empty($this->_sessionId))
		{
			$headers['sessionId'] = $this->_sessionId;
		}
		$contextNS = ($this->_namespace === 'urn:zimbraAdmin') ? $this->_namespace : 'urn:zimbra';
		$this->_soapHeader = new SoapHeader($contextNS, 'context', $headers);

		$this->__setSoapHeaders($this->_soapHeader);
		$request = $this->_filterAttributes(ltrim($request));

		if ($this->_filters)
		{
			foreach ($this->_filters as $callback)
			{
				$request = call_user_func($callback, $request);
			}
		}

		return parent::__doRequest($request, $location, $action, $version, $one_way);
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
			return $this->_authToken;
		}
		$this->_authToken = (string) $authToken;
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
			return $this->_sessionId;
		}
		$this->_sessionId = (string) $sessionId;
		return $this;
	}

	/**
	 * Set or get soap header.
	 *
	 * @param  SoapHeader $soapHeader Soap header
	 * @throws ZAP_Exception
	 * @return ZAP_Client_Soap
	 */
	public function soapHeader(SoapHeader $soapHeader)
	{
		if($soapHeader === NULL)
		{
			return $this->_soapHeader;
		}
		$this->_soapHeader = $soapHeader;
		return $this;
	}

	/**
	 * Performs a SOAP requestt
	 *
	 * @param  string $name       The soap function.
	 * @param  string $params     The soap parameters.
	 * @param  string $attributes The soap attributes.
	 * @throws SoapFault
	 * @return soap response
	 */
	public function soapRequest($name, array $params = array(), array $attributes = array())
	{
		$this->_soapAttributes['name'] = $name;
		$this->_soapAttributes['attributes'] = $attributes;
		$soapParams = array();
		foreach ($params as $key => $value)
		{
			if (is_array($value))
			{
				$xml = ZAP_Helpers::arrayToXml('SoapVar', array($key => $value));
				$soapParams[] = new SoapVar($xml->children()->asXml(), XSD_ANYXML);
			}
			else
			{
				$soapParams[] = new SoapParam($value, $key);
			}
		}
		try
		{
			$this->__soapCall($name, $soapParams);
			return $this->_processResponse($this->__getLastResponse());
		}
		catch(SoapFault $exception)
		{
			throw($exception);
		}
	}

	/**
	 * Process soap response body.
	 *
	 * @param  string $soapMessage Soap response message.
	 * @return mix
	 */
	private function _processResponse($soapMessage)
	{
		$xml = new SimpleXMLElement($soapMessage);
		return ZAP_Helpers::xmlToObject($xml->children('soap', TRUE)->Body);
	}

	/**
	 * Filter soap request.
	 *
	 * @param  string $request The XML SOAP request.
	 * @return string.
	 */
	private function _filterAttributes($request)
	{
		$name = $this->_soapAttributes['name'];
		$attributes = $this->_soapAttributes['attributes'];
		if(count($attributes))
		{
			$dom = new DomDocument('1.0', 'UTF-8');
			$dom->loadXML($request);
			$node = $dom->getElementsByTagName($name)->item(0);
			foreach ($attributes as $key => $value)
			{
				if(ZAP_Helpers::isValidTagName($key))
				{
					if($node)
					{
						$node->setAttribute($key, ZAP_Helpers::boolToString($value));						
					}
				}
			}
			$request = $dom->saveXml();
		}
		return $request;
	}
}

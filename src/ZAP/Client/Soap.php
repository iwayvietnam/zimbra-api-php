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
class ZAP_Client_Soap extends SoapClient implements ZAP_Client_Interface
{
	/**
	 * @var string Authentication token
	 */
	private $_authToken;

	/**
	 * @var string Authentication identify
	 */
	private $_sessionId;

	/**
	 * @var string Soap namespace
	 */
	private $_namespace = 'urn:zimbra';

	/**
	 * @var array filter callbacks
	 */
	private $_filters = array();

	/**
	 * @var array soap function attributes
	 */
	private $_soapAttributes = array();

	/**
	 * ZAP_Client_Soap constructor
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
		$this->__setSoapHeaders(new SoapHeader('urn:zimbra', 'context', $authVar));
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
		$request = $this->_filterRequest(ltrim($request));

		if ($this->_filters)
		{
			foreach ($this->_filters as $callback)
			{
				$request = call_user_func($callback, $request);
			}
		}

		$this->__last_request = $request;
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
	 * Performs a SOAP request
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
				$xmlString = '';
				foreach ($xml->children() as $child)
				{
					$xmlString .= $child->asXml();
				}
				$soapParams[] = new SoapVar($xmlString, XSD_ANYXML);
			}
			else
			{
				$soapParams[] = new SoapParam($value, $key);
			}
		}
		$this->__soapCall($name, $soapParams);
		return $this->_processResponse($this->__getLastResponse());
	}

	/**
	 * Returns last SOAP request.
	 *
	 * @return The last SOAP request, as an XML string.
	 */
	public function lastRequest()
	{
		return $this->__getLastRequest();
	}

	/**
	 * Returns the SOAP headers from the last request.
	 *
	 * @return The last SOAP request headers.
	 */
	public function lastRequestHeaders()
	{
		return $this->_extractHeaders($this->__getLastRequestHeaders());
	}

	/**
	 * Returns last SOAP response.
	 *
	 * @return The last SOAP response, as an XML string.
	 */
	public function lastResponse()
	{
		return $this->__getLastResponse();
	}

	/**
	 * Returns the SOAP headers from the last response.
	 *
	 * @return The last SOAP response headers.
	 */
	public function lastResponseHeaders()
	{
		return $this->_extractHeaders($this->__getLastResponseHeaders());
	}

	/**
	 * Process soap response body.
	 *
	 * @param  string $soapMessage Soap response message.
	 * @return mix
	 */
	private function _processResponse($soapMessage)
	{
		$xml = simplexml_load_string($soapMessage);
		return ZAP_Helpers::xmlToObject($xml->children('soap', TRUE)->Body);
	}

	/**
	 * Filter soap request.
	 *
	 * @param  string $request The XML SOAP request.
	 * @return string.
	 */
	private function _filterRequest($request)
	{
		$xml = simplexml_load_string($request);
		$header = $xml->children('env', TRUE)->Header;
		$context = NULL;
		foreach ($header->children('urn:zimbra') as $child)
		{
			if($child->getName() === 'context')
			{
				$context = $child;
			}
		}
		if($context instanceof SimpleXMLElement)
		{
			if(!empty($this->_authToken))
			{
				$context->addChild('authToken', $this->_authToken, $this->_namespace);
			}
			if(!empty($this->_sessionId))
			{
				$context->addChild('sessionId', $this->_sessionId, $this->_namespace);
			}
		}

		$name = $this->_soapAttributes['name'];
		$attributes = $this->_soapAttributes['attributes'];

		$body = $xml->children('env', TRUE)->Body;
		$soapFunction = NULL;
		foreach ($body->children($this->_namespace) as $child)
		{
			if($child->getName() === $name)
			{
				$soapFunction = $child;
			}
		}
		if($soapFunction instanceof SimpleXMLElement)
		{
			foreach ($attributes as $key => $value)
			{
				$soapFunction->addAttribute($key, ZAP_Helpers::boolToString($value));
			}
		}
		$request = $xml->asXml();

		return $request;
	}

	private function _extractHeaders($headerString = '')
	{
		$responses = explode("\r\n", $headerString);
		$headers = array();
		foreach ($responses as $response)
		{
			$pos = strpos($response, ':');
			if($pos)
			{
				$name = trim(substr($response, 0, $pos));
				$value = trim(substr($response, ($pos + 1)));
				$headers[$name] = $value;
			}
		}
		return $headers;
	}
}

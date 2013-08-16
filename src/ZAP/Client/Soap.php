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
 * The ZAP_Client_Soap class provides a client for SOAP 1.2 servers.
 * It be used in non-WSDL mode.
 * @package   ZAP
 * @category  Client
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
class ZAP_Client_Soap extends ZAP_Client_Soap_Base implements ZAP_Client_Interface
{
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
	public function __construct($location, $namespace = 'urn:zimbra')
	{
		parent::__construct($location, $namespace, FALSE);
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
	 * Performs a SOAP request
	 *
	 * @param  string $name   The soap function.
	 * @param  string $params The soap parameters.
	 * @param  string $attrs  The soap attributes.
	 * @throws SoapFault
	 * @return object Soap response
	 */
	public function soapRequest($name, array $params = array(), array $attrs = array())
	{
		$this->_soapAttributes['name'] = $name;
		$this->_soapAttributes['attributes'] = $attrs;
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

		$soapHeader = $this->soapHeader();
		if($soapHeader instanceof SoapHeader)
		{
			$this->__soapCall($name, $soapParams, NULL, $soapHeader);
		}
		else
		{
			$this->__soapCall($name, $soapParams);
		}
		$xml = simplexml_load_string($this->lastResponse());
		return ZAP_Helpers::xmlToObject($xml->children('soap', TRUE)->Body);
	}

	/**
	 * Filter soap request.
	 *
	 * @param  string $request The XML SOAP request.
	 * @return string The XML SOAP request.
	 */
	protected function _filterRequest($request)
	{
		$xml = simplexml_load_string($request);
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
}

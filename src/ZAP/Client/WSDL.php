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
 * The ZAP_Client_WSDL class provides a client for SOAP 1.2 servers.
 * It be used in WSDL mode.
 * @package   ZAP
 * @category  Client
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
class ZAP_Client_WSDL extends ZAP_Client_Soap_Base implements ZAP_Client_Interface
{
	/**
	 * ZAP_Client_Soap constructor
	 *
	 * @param string $location  The URL to request.
	 * @param string $namespace The SOAP namespace.
	 */
	public function __construct($location, $namespace = 'urn:zimbra')
	{
		parent::__construct($location, $namespace, TRUE);
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
		$request = ucfirst($name).'Request';
		$params = $attrs = array();
		if(isset($args[0]))
		{
			$params = is_array($args[0]) ? $args[0] : array($args[0]);
		}
		if(isset($args[1]))
		{
			$attrs = is_array($args[1]) ? $args[1] : array($args[1]);
		}
		return $this->soapRequest($request, $params, $attrs);
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
		$soapHeader = $this->soapHeader();
		$parameters = array('parameters' => $params + $attrs);
		if($this->_soapHeader instanceof SoapHeader)
		{
			return $this->__soapCall($name, $parameters, NULL, $soapHeader);
		}
		else
		{
			return $this->__soapCall($name, $parameters);
		}
	}
}
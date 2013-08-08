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
 * ZAP_Client_CURL is a class which provides a curl client for SOAP servers
 * @package   ZAP
 * @category  Client
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
class ZAP_Client_CURL extends ZAP_Client_Base implements ZAP_Client_Interface
{
	/**
	 * @var resource CURL resource handle
	 */
	protected $_curl;

	/**
	 * ZAP_Client_CURL constructor
	 *
	 * @param string $location  The URL to request.
	 * @param string $namespace The SOAP namespace.
	 */
	public function __construct($location, $namespace = 'urn:zimbra')
	{
		parent::__construct($location, $namespace);

		$this->_curl = curl_init();
		curl_setopt($this->_curl, CURLOPT_URL, $location);
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT, 30);
	}

	/**
	 * Performs a SOAP requestt
	 *
	 * @param  string $name       The soap function.
	 * @param  string $params     The soap parameters.
	 * @param  string $attributes The soap attributes.
	 * @return soap response
	 */
	public function soapRequest($name, array $params = array(), array $attributes = array())
	{
		$this->_soapMessage->setBody($name, $attributes, $params);
		$headers = array(
			'Content-Type: text/xml; charset=utf-8',
			'Method: POST',
			'User-Agent: '.$_SERVER['HTTP_USER_AGENT'],
			'SoapAction: '.$this->_soapMessage->getNamespace().'#'.$name
		);
		curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($this->_curl, CURLOPT_POSTFIELDS, (string) $this->_soapMessage);
		return $this->_soapMessage->processResponse(curl_exec($this->_curl));
	}
}
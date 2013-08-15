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
	private $_curl;

	/**
	 * @var string Response header string
	 */
	private $_responseHeader;

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
		curl_setopt($this->_curl, CURLINFO_HEADER_OUT, TRUE);
		curl_setopt($this->_curl, CURLOPT_HEADERFUNCTION, array($this, "_readHeader"));

		$temp = tmpfile();
		curl_setopt($this->_curl, CURLOPT_COOKIEFILE, $temp);
		curl_setopt($this->_curl, CURLOPT_COOKIEJAR, $temp);
	}

	/**
	 * Performs a SOAP request
	 *
	 * @param  string $name   The soap function.
	 * @param  string $params The soap parameters.
	 * @param  string $attrs  The soap attributes.
	 * @return mix Soap response
	 */
	public function soapRequest($name, array $params = array(), array $attrs = array())
	{
		$this->_responseHeader = '';
		$this->_soapMessage->setBody($name, $attrs, $params);
		$this->_headers['SoapAction'] = $this->_soapMessage->getNamespace().'#'.$name;
		$headers = array();
		foreach ($this->_headers as $key => $value)
		{
			$headers[] = $key.': '.$value;
		}
		curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($this->_curl, CURLOPT_POSTFIELDS, (string) $this->_soapMessage);
		$this->_response = curl_exec($this->_curl);
		return $this->_soapMessage->processResponse($this->_response);
	}

	/**
	 * Returns the SOAP headers from the last request.
	 *
	 * @return mix The last SOAP request headers.
	 */
	function lastRequestHeaders()
	{
		return ZAP_Helpers::extractHeaders(curl_getinfo($this->_curl, CURLINFO_HEADER_OUT));
	}

	/**
	 * Returns the SOAP headers from the last response.
	 *
	 * @return mix The last SOAP response headers.
	 */
	public function lastResponseHeaders()
	{
		return ZAP_Helpers::extractHeaders($this->_responseHeader);
	}

	/**
	 * CURL callback for reading headers.
	 *
	 * @return mix
	 */
	protected function _readHeader($curl, $header)
	{
		$this->_responseHeader .= $header;
		return strlen($header);
	}
}
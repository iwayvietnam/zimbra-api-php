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
 * ZAP_Client_HTTP is a class which provides a http client for SOAP servers
 * @package   ZAP
 * @category  Client
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
class ZAP_Client_Socket extends ZAP_Client_Base implements ZAP_Client_Interface
{
	/**
	 * @var ZAP_HTTP_Request
	 */
	protected $_httpRequest;

	/**
	 * @var string Response header string
	 */
	private $_responseHeader;

	/**
	 * ZAP_Client_HTTP constructor
	 *
	 * @param string $location  The URL to request.
	 * @param string $namespace The SOAP namespace.
	 */
	public function __construct($location, $namespace = 'urn:zimbra')
	{
		parent::__construct($location, $namespace);
		$this->_httpRequest = new ZAP_HTTP_Request($location, ZAP_HTTP_Request::METHOD_POST);
		$this->_httpRequest->config(array(
			'ssl_verify_peer' => FALSE,
			'ssl_verify_host' => FALSE,
		));
	}

	/**
	 * Performs a SOAP request
	 *
	 * @param  string $name   The soap function.
	 * @param  string $params The soap parameters.
	 * @param  string $attrs  The soap attributes.
	 * @return object Soap response
	 */
	public function soapRequest($name, array $params = array(), array $attrs = array())
	{
		$this->_headers['SoapAction'] = $this->_soapMessage->getNamespace().'#'.$name;
		$this->_httpRequest->header($this->_headers);
		$this->_soapMessage->setBody($name, $attrs, $params);
		$this->_httpRequest->body((string) $this->_soapMessage);
		$response = $this->_httpRequest->send();
		$this->_responseHeader = $response->header();
		$this->_response = $response->body();
		return $this->_soapMessage->processResponse($this->_response);
	}

	/**
	 * Returns the SOAP headers from the last request.
	 *
	 * @return array The last SOAP request headers.
	 */
	function lastRequestHeaders()
	{
		return $this->_httpRequest->headers();
	}

	/**
	 * Returns the SOAP headers from the last response.
	 *
	 * @return array The last SOAP response headers.
	 */
	public function lastResponseHeaders()
	{
		return $this->_responseHeader;
	}
}

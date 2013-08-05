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
 * ZAP_Client_HTTP is a class which provides a http client for SOAP servers
 * @package   ZAP
 * @category  Client
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
class ZAP_Client_HTTP extends ZAP_Client_Base implements ZAP_Client_IClient
{
	/**
	 * @var HttpRequest
	 */
	protected $_httpRequest;

	/**
	 * ZAP_Client_HTTP constructor
	 *
	 * @param string $location  The URL to request.
	 * @param string $namespace The SOAP namespace.
	 */
	public function __construct($location, $namespace = 'urn:zimbra')
	{
		parent::__construct($location, $namespace);
		$this->_httpRequest = new HttpRequest($location, HttpRequest::METH_POST);
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
		$headers = array(
			'Content-Type' => 'text/xml; charset=utf-8',
			'Method'       => 'POST',
			'User-Agent'   => 'PHP-SOAP-HttpRequest',
		);
		$this->_httpRequest->addHeaders($headers);
		$this->_soapMessage->setBody($name, $attributes, $params);
		$this->_httpRequest->setBody((string) $this->_soapMessage);
		$response = $this->_httpRequest->send();
		return $this->_soapMessage->processResponse($this->_httpRequest->getResponseBody());
	}
}
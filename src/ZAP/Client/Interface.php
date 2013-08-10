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
 * ZAP_Client_Interface is a interface which provides a client for SOAP servers
 * @package   ZAP
 * @category  Client
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
interface ZAP_Client_Interface
{
	/**
	 * Set or get authentication token.
	 *
	 * @param  string $authToken Authentication token
	 * @return ZAP_Client_Soap
	 */
	function authToken($authToken = NULL);

	/**
	 * Set or get authentication session identify.
	 *
	 * @param  string $sessionId Authentication session identify
	 * @return ZAP_Client_Soap
	 */
	function sessionId($sessionId = NULL);

	/**
	 * Performs a SOAP request
	 *
	 * @param  string $name       The soap function.
	 * @param  string $params     The soap parameters.
	 * @param  string $attributes The soap attributes.
	 * @return soap response
	 */
	function soapRequest($name, array $params = array(), array $attributes = array());

	/**
	 * Returns last SOAP request.
	 *
	 * @return The last SOAP request, as an XML string.
	 */
	function lastRequest();

	/**
	 * Returns the SOAP headers from the last request.
	 *
	 * @return The last SOAP request headers.
	 */
	function lastRequestHeaders();

	/**
	 * Returns last SOAP response.
	 *
	 * @return The last SOAP response, as an XML string.
	 */
	function lastResponse();

	/**
	 * Returns the SOAP headers from the last response.
	 *
	 * @return The last SOAP response headers.
	 */
	function lastResponseHeaders();
}
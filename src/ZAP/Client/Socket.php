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
 * ZAP_Client_Socket is a class which provides a socket client for SOAP servers
 * @package   ZAP
 * @category  Client
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
class ZAP_Client_Socket extends ZAP_Client_Base implements ZAP_Client_Interface
{
	/**
	 * @var resource socket resource handle
	 */
	private $_socket;

	/**
	 * @var array parse url
	 */
	private $_uri;

	/**
	 * ZAP_Client_Socket constructor
	 *
	 * @param string $location  The URL to request.
	 * @param string $namespace The SOAP namespace.
	 */
	public function __construct($location, $namespace = 'urn:zimbra')
	{
		parent::__construct($location, $namespace);		
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
			'Content-Type' => 'text/xml; charset=utf-8',
			'Method'       => 'POST',
			'User-Agent'   => $_SERVER['HTTP_USER_AGENT'],
			'SoapAction'   => $this->_soapMessage->getNamespace().'#'.$name
		);
		$response = $this->_request((string) $this->_soapMessage, $headers);
		return $this->_soapMessage->processResponse($response);
	}

	private function _request($data = NULL, array $headers = array())
	{
		$this->_connect();
		$path = $this->_uri['path'];
		if(isset($this->_uri['query']) AND !empty($this->_uri['query']))
		{
			$path .= '?'.$this->_uri['query'];
		}
		if (!empty($data))
		{
			if (!isset($headers['Content-Type']))
			{
				$headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=utf-8';
			}
			$headers['Content-Length'] = strlen($data);
		}

		$request = array();
		$request[] = 'POST '.((empty($path))?'/':$path).' HTTP/1.0';
		$request[] = 'Host: '.$this->_uri['host'];
		if (is_array($headers))
		{
			foreach ($headers as $key => $value)
			{
				$request[] = $key.': '.$value;
			}
		}
		if (!empty($data))
		{
			$request[] = NULL;
			$request[] = $data;
		}
		fwrite($this->_socket, implode("\r\n", $request)."\r\n");

		while (($line = @fgets($this->_socket)) !== false)
		{
			$content .= $line;			
		}
		$this->_close();
		return $this->_response($content);
	}

	private function _response($content)
	{
		if (empty($content))
		{
			throw new UnexpectedValueException('No content in response.');
		}

		$response = explode("\r\n\r\n", $content, 2);
		return empty($response[1]) ? '' : $response[1];
	}

	private function _connect($timeout = 30.0)
	{
		$this->_uri = $this->_parseUrl($this->_location);
		if($this->_uri)
		{
			$isSSL = $this->_uri['scheme'] === 'https';
			$host = ($isSSL) ? 'ssl://' . $this->_uri['host'] : $this->_uri['host'];
			if(!isset($this->_uri['port']))
			{
				$port = ($isSSL) ? 443 : 80;
			}
			else
			{
				$port = $this->_uri['port'];
			}

			if (!is_numeric($timeout))
			{
				$timeout = ini_get('default_socket_timeout');
			}
			$context = stream_context_create();
			if($isSSL)
			{
				stream_context_set_option($context, 'ssl', 'verify_host', true);
				stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
			}

			$this->_socket = @stream_socket_client($host.':'.$port, $errno, $err, (float) $timeout, STREAM_CLIENT_CONNECT, $context);
			if (!$this->_socket)
			{
				$this->_close();
				$errormsg = 'Unable to connect to ' . $host . ':' . $port . '. Error #' . $errno . ': ' . $errstr;
				throw new RuntimeException($errormsg);
			}
			if(!stream_set_timeout($this->_socket, (int) $timeout))
			{
				throw new RuntimeException('Unable to set the connection timeout');
			}
		}
		else
		{
			$errormsg = 'Could not parse url: '.$this->_location;
			throw new RuntimeException($errormsg);
		}
	}

	private function _close()
	{
		if (is_resource($this->_socket))
		{
			@fclose($this->_socket);
		}
		$this->_socket = NULL;
	}

	private function _parseUrl($url)
	{
		$result = false;

		$entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%24', '%2C', '%2F', '%3F', '%23', '%5B', '%5D');
		$replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "$", ",", "/", "?", "#", "[", "]");

		$encodedURL = str_replace($entities, $replacements, urlencode($url));
		$parts = parse_url($encodedURL);
		if ($parts)
		{
			foreach ($parts as $key => $value)
			{
				$result[$key] = urldecode(str_replace($replacements, $entities, $value));
			}
		}
		return $result;		
	}
}
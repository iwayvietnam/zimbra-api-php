<?php
/**
 * Base class for Zimbra\Request adapters
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2008-2012, Alexey Borzov <avb@php.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Adapter for Request wrapping around cURL extension
 * 
 * @package  Zimbra
 * @category Http
 * @author   Alexey Borzov <avb@php.net>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 */
class ZAP_HTTP_Adapter_Curl extends ZAP_HTTP_Adapter
{
	/**
	 * Mapping of header names to cURL options
	 * @var  array
	 */
	protected static $headerMap = array(
		'accept-encoding' => CURLOPT_ENCODING,
		'cookie'          => CURLOPT_COOKIE,
		'referer'         => CURLOPT_REFERER,
		'user-agent'      => CURLOPT_USERAGENT,
	);

	/**
	 * Mapping of SSL context options to cURL options
	 * @var  array
	 */
	protected static $sslContextMap = array(
		'ssl_verify_peer' => CURLOPT_SSL_VERIFYPEER,
		'ssl_cafile'      => CURLOPT_CAINFO,
		'ssl_capath'      => CURLOPT_CAPATH,
		'ssl_local_cert'  => CURLOPT_SSLCERT,
		'ssl_passphrase'  => CURLOPT_SSLCERTPASSWD,
	);

	/**
	 * Response being received
	 * @var  Response
	 */
	protected $response;

	/**
	 * Position within request body
	 * @var  integer
	 * @see  callbackReadBody()
	 */
	protected $position = 0;

	/**
	 * Information about last transfer, as returned by curl_getinfo()
	 * @var  array
	 */
	protected $lastInfo;

	/**
	 * Creates a Exception from curl error data
	 *
	 * @param resource $ch curl handle
	 *
	 * @return Exception
	 */
	protected static function wrapCurlError($ch)
	{
		$nativeCode = curl_errno($ch);
		$message    = 'Curl error: ' . curl_error($ch);
		return new RuntimeException($message, $nativeCode);
	}

	/**
	 * Sends request to the remote server and returns its response
	 *
	 * @param Request $request HTTP request message
	 *
	 * @return Response
	 * @throws Exception
	 */
	public function sendRequest(ZAP_HTTP_Request $request)
	{
		if (!extension_loaded('curl'))
		{
			throw new LogicException('cURL extension not available');
		}

		$this->request  = $request;
		$this->response = NULL;
		$this->position = 0;

		try
		{
			if (FALSE === curl_exec($ch = $this->createCurlHandle()))
			{
				$e = self::wrapCurlError($ch);
			}
		} catch (Exception $e) {}
		if (isset($ch))
		{
			$this->lastInfo = curl_getinfo($ch);
			curl_close($ch);
		}

		$response = $this->response;
		unset($this->request, $this->requestBody, $this->response);

		if (!empty($e))
		{
			throw $e;
		}

		if ($jar = $request->cookieJar())
		{
			$jar->addCookiesFromResponse($response, $request->url());
		}
		return $response;
	}

	/**
	 * Returns information about last transfer
	 *
	 * @return   array   associative array as returned by curl_getinfo()
	 */
	public function info()
	{
		return $this->lastInfo;
	}

	/**
	 * Creates a new cURL handle and populates it with data from the request
	 *
	 * @return resource    a cURL handle, as created by curl_init()
	 * @throws LogicException
	 */
	protected function createCurlHandle()
	{
		$ch = curl_init();

		curl_setopt_array($ch, array(
			// setup write callbacks
			CURLOPT_HEADERFUNCTION => array($this, 'callbackWriteHeader'),
			CURLOPT_WRITEFUNCTION  => array($this, 'callbackWriteBody'),
			// buffer size
			CURLOPT_BUFFERSIZE     => $this->request->config('buffer_size'),
			// connection timeout
			CURLOPT_CONNECTTIMEOUT => $this->request->config('connect_timeout'),
			// save full outgoing headers, in case someone is interested
			CURLINFO_HEADER_OUT    => TRUE,
			// request url
			CURLOPT_URL            => $this->request->url()->url()
		));

		// set up redirects
		if (!$this->request->config('follow_redirects'))
		{
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
		}
		else
		{
			if (!@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE))
			{
				throw new LogicException(
					'Redirect support in curl is unavailable due to open_basedir or safe_mode setting'
				);
			}
			curl_setopt($ch, CURLOPT_MAXREDIRS, $this->request->config('max_redirects'));
			// limit redirects to http(s), works in 5.2.10+
			if (defined('CURLOPT_REDIR_PROTOCOLS'))
			{
				curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
			}
			// works in 5.3.2+, http://bugs.php.net/bug.php?id=49571
			if ($this->request->config('strict_redirects') AND defined('CURLOPT_POSTREDIR'))
			{
				curl_setopt($ch, CURLOPT_POSTREDIR, 3);
			}
		}

		// request timeout
		if ($timeout = $this->request->config('timeout'))
		{
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		}

		// set HTTP version
		switch ($this->request->config('protocol_version'))
		{
			case '1.0':
				curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
				break;
			case '1.1':
				curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
				break;
		}

		// set request method
		switch ($this->request->method())
		{
			case ZAP_HTTP_Request::METHOD_GET:
				curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
				break;
			case ZAP_HTTP_Request::METHOD_POST:
				curl_setopt($ch, CURLOPT_POST, TRUE);
				break;
			case ZAP_HTTP_Request::METHOD_HEAD:
				curl_setopt($ch, CURLOPT_NOBODY, TRUE);
				break;
			case ZAP_HTTP_Request::METHOD_PUT:
				curl_setopt($ch, CURLOPT_UPLOAD, TRUE);
				break;
			default:
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->request->method());
		}

		// set proxy, if needed
		if ($host = $this->request->config('proxy_host'))
		{
			if (!($port = $this->request->config('proxy_port')))
			{
				throw new LogicException('Proxy port not provided');
			}
			curl_setopt($ch, CURLOPT_PROXY, $host . ':' . $port);
			if ($user = $this->request->config('proxy_user'))
			{
				curl_setopt(
					$ch, CURLOPT_PROXYUSERPWD,
					$user . ':' . $this->request->config('proxy_password')
				);
				switch ($this->request->config('proxy_auth_scheme'))
				{
					case ZAP_HTTP_Request::AUTH_BASIC:
						curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
						break;
					case ZAP_HTTP_Request::AUTH_DIGEST:
						curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_DIGEST);
						break;
				}
			}
			if ($type = $this->request->config('proxy_type'))
			{
				switch ($type)
				{
					case 'http':
						curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
						break;
					case 'socks5':
						curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
						break;
					default:
						throw new LogicException ("Proxy type '{$type}' is not supported");
				}
			}
		}

		// set authentication data
		if ($auth = $this->request->auth())
		{
			curl_setopt($ch, CURLOPT_USERPWD, $auth['user'] . ':' . $auth['password']);
			switch ($auth['scheme'])
			{
				case ZAP_HTTP_Request::AUTH_BASIC:
					curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
					break;
				case ZAP_HTTP_Request::AUTH_DIGEST:
					curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
					break;
			}
		}

		// set SSL options
		foreach ($this->request->config() as $name => $value)
		{
			if ('ssl_verify_host' == $name AND NULL !== $value)
			{
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $value ? 2 : 0);
			}
			elseif (isset(self::$sslContextMap[$name]) AND NULL !== $value)
			{
				curl_setopt($ch, self::$sslContextMap[$name], $value);
			}
		}

		$headers = $this->request->headers();
		// make cURL automagically send proper header
		if (!isset($headers['accept-encoding']))
		{
			$headers['accept-encoding'] = '';
		}

		if (($jar = $this->request->cookieJar()) AND ($cookies = $jar->matching($this->request->url(), TRUE)))
		{
			$headers['cookie'] = (empty($headers['cookie'])? '': $headers['cookie'] . '; ') . $cookies;
		}

		// set headers having special cURL keys
		foreach (self::$headerMap as $name => $option)
		{
			if (isset($headers[$name]))
			{
				curl_setopt($ch, $option, $headers[$name]);
				unset($headers[$name]);
			}
		}

		$this->calculateRequestLength($headers);
		if (isset($headers['content-length']))
		{
			$this->workaroundPhpBug47204($ch, $headers);
		}

		// set headers not having special keys
		$headersFmt = array();
		foreach ($headers as $name => $value)
		{
			$canonicalName = implode('-', array_map('ucfirst', explode('-', $name)));
			$headersFmt[]  = $canonicalName . ': ' . $value;
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headersFmt);

		return $ch;
	}

	/**
	 * Workaround for PHP bug #47204 that prevents rewinding request body
	 *
	 * The workaround consists of reading the entire request body into memory
	 * and setting it as CURLOPT_POSTFIELDS, so it isn't recommended for large
	 * file uploads, use Socket adapter instead.
	 *
	 * @param resource $ch       cURL handle
	 * @param array    &$headers Request headers
	 */
	protected function workaroundPhpBug47204($ch, &$headers)
	{
		// no redirects, no digest auth -> probably no rewind needed
		if (!$this->request->config('follow_redirects') AND (!($auth = $this->request->auth())
			OR ZAP_HTTP_Request::AUTH_DIGEST != $auth['scheme'])
		)
		{
			curl_setopt($ch, CURLOPT_READFUNCTION, array($this, 'callbackReadBody'));
		}
		else
		{
			// rewind may be needed, read the whole body into memory
			if ($this->requestBody instanceof ZAP_HTTP_Multipart)
			{
				$this->requestBody = $this->requestBody->__toString();

			}
			elseif (is_resource($this->requestBody))
			{
				$fp = $this->requestBody;
				$this->requestBody = '';
				while (!feof($fp))
				{
					$this->requestBody .= fread($fp, 16384);
				}
			}
			// curl hangs up if content-length is present
			unset($headers['content-length']);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);
		}
	}

	/**
	 * Callback function called by cURL for reading the request body
	 *
	 * @param resource $ch     cURL handle
	 * @param resource $fd     file descriptor (not used)
	 * @param integer  $length maximum length of data to return
	 *
	 * @return   string      part of the request body, up to $length bytes
	 */
	protected function callbackReadBody($ch, $fd, $length)
	{
		if (in_array($this->request->method(), self::$bodyDisallowed) OR 0 == $this->contentLength OR $this->position >= $this->contentLength)
		{
			return '';
		}
		$bufferSize = $this->request->config('buffer_size');
		if (is_string($this->requestBody))
		{
			$string = substr($this->requestBody, $this->position, $length);
		}
		elseif (is_resource($this->requestBody))
		{
			$string = fread($this->requestBody, $length);
		}
		else
		{
			$string = $this->requestBody->read($length);
		}
		$this->position += strlen($string);
		return $string;
	}

	/**
	 * Callback function called by cURL for saving the response headers
	 *
	 * @param resource $ch     cURL handle
	 * @param string   $string response header (with trailing CRLF)
	 *
	 * @return integer     number of bytes saved
	 * @see    Response::parseHeaderLine()
	 */
	protected function callbackWriteHeader($ch, $string)
	{
		if (empty($this->response))
		{
			$this->response = new ZAP_HTTP_Response(
				$string, FALSE, curl_getinfo($ch, CURLINFO_EFFECTIVE_URL)
			);
		}
		else
		{
			$this->response->parseHeaderLine($string);
			if ('' == trim($string))
			{
				if ($this->request->config('follow_redirects') AND $this->response->isRedirect())
				{
					$redirectUrl = new ZAP_HTTP_Url($this->response->header('location'));

					// for versions lower than 5.2.10, check the redirection URL protocol
					if (!defined('CURLOPT_REDIR_PROTOCOLS') AND $redirectUrl->isAbsolute()
						AND !in_array($redirectUrl->scheme(), array('http', 'https'))
					)
					{
						return -1;
					}

					if ($jar = $this->request->cookieJar())
					{
						$jar->addCookiesFromResponse($this->response, $this->request->url());
						if (!$redirectUrl->isAbsolute())
						{
							$redirectUrl = $this->request->url()->resolve($redirectUrl);
						}
						if ($cookies = $jar->matching($redirectUrl, TRUE))
						{
							curl_setopt($ch, CURLOPT_COOKIE, $cookies);
						}
					}
				}
			}
		}
		return strlen($string);
	}

	/**
	 * Callback function called by cURL for saving the response body
	 *
	 * @param resource $ch     cURL handle (not used)
	 * @param string   $string part of the response body
	 *
	 * @return integer     number of bytes saved
	 * @throws LogicException
	 * @see    Response::appendBody()
	 */
	protected function callbackWriteBody($ch, $string)
	{
		// cURL calls WRITEFUNCTION callback without calling HEADERFUNCTION if
		// response doesn't start with proper HTTP status line (see bug #15716)
		if (empty($this->response))
		{
			throw new LogicException("Malformed response: {$string}");
		}
		if ($this->request->config('store_body'))
		{
			$this->response->appendBody($string);
		}
		return strlen($string);
	}
}

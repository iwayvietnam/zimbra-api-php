<?php
/**
 * Class representing a HTTP request message
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
 * Class representing a HTTP request message
 * 
 * @package  Zimbra
 * @category Http
 * @author	 Alexey Borzov <avb@php.net>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 */
class ZAP_HTTP_Request
{
	/**
	 * Constants for HTTP request methods
	 */
	const METHOD_OPTIONS = 'OPTIONS';
	const METHOD_GET     = 'GET';
	const METHOD_HEAD    = 'HEAD';
	const METHOD_POST    = 'POST';
	const METHOD_PUT     = 'PUT';
	const METHOD_DELETE  = 'DELETE';
	const METHOD_TRACE   = 'TRACE';
	const METHOD_CONNECT = 'CONNECT';

	/**
	 * Constants for HTTP authentication schemes
	 */
	const AUTH_BASIC  = 'basic';
	const AUTH_DIGEST = 'digest';

	/**
	 * Regular expression used to check for invalid symbols in RFC 2616 tokens
	 * @link http://pear.php.net/bugs/bug.php?id=15630
	 */
	const REGEXP_INVALID_TOKEN = '![\x00-\x1f\x7f-\xff()<>@,;:\\\\"/\[\]?={}\s]!';

	/**
	 * Regular expression used to check for invalid symbols in cookie strings
	 * @link http://pear.php.net/bugs/bug.php?id=15630
	 */
	const REGEXP_INVALID_COOKIE = '/[\s,;]/';

	/**
	 * Fileinfo magic database resource
	 * @var  resource
	 * @see  detectMimeType()
	 */
	private static $_fileinfoDb;

	/**
	 * Request URL
	 * @var  Url
	 */
	protected $url;

	/**
	 * Request method
	 * @var  string
	 */
	protected $method = self::METHOD_GET;

	/**
	 * Authentication data
	 * @var  array
	 * @see  auth()
	 */
	protected $auth;

	/**
	 * Request headers
	 * @var  array
	 */
	protected $headers = array();

	/**
	 * Configuration parameters
	 * @var  array
	 * @see  setConfig()
	 */
	protected $config = array(
		'adapter'		    => 'socket',
		'connect_timeout'   => 30,
		'timeout'		    => 0,
		'use_brackets'	    => TRUE,
		'protocol_version'  => '1.1',
		'buffer_size'	    => 16384,
		'store_body'		=> TRUE,

		'proxy_host'		=> '',
		'proxy_port'		=> '',
		'proxy_user'		=> '',
		'proxy_password'	=> '',
		'proxy_auth_scheme' => self::AUTH_BASIC,
		'proxy_type'		=> 'http',

		'ssl_verify_peer'   => TRUE,
		'ssl_verify_host'   => TRUE,
		'ssl_cafile'		=> NULL,
		'ssl_capath'		=> NULL,
		'ssl_local_cert'	=> NULL,
		'ssl_passphrase'	=> NULL,

		'digest_compat_ie'  => FALSE,

		'follow_redirects'  => FALSE,
		'max_redirects'	    => 5,
		'strict_redirects'  => FALSE
	);

	/**
	 * Request body
	 * @var  string|resource
	 * @see  body()
	 */
	protected $body = '';

	/**
	 * Array of POST parameters
	 * @var  array
	 */
	protected $postParams = array();

	/**
	 * Array of file uploads (for multipart/form-data POST requests)
	 * @var  array
	 */
	protected $uploads = array();

	/**
	 * Adapter used to perform actual HTTP request
	 * @var  adapter
	 */
	protected $adapter;

	/**
	 * Cookie jar to persist cookies between requests
	 * @var cookieJar
	 */
	protected $cookieJar = NULL;

	/**
	 * Constructor. Can set request URL, method and configuration array.
	 *
	 * Also sets a default value for User-Agent header.
	 *
	 * @param string|Url $url    Request URL
	 * @param string     $method Request method
	 * @param array      $config Configuration for this Request instance
	 */
	public function __construct($url = NULL, $method = self::METHOD_GET, array $config = array())
	{
		$this->config($config);
		if (!empty($url))
		{
			$this->url($url);
		}
		if (!empty($method))
		{
			$this->method($method);
		}
		$this->header('user-agent', $_SERVER['HTTP_USER_AGENT']);
	}

	/**
	 * Gets or sets the URL for this request
	 *
	 * If the URL has userinfo part (username & password) these will be removed
	 * and converted to auth data. If the URL does not have a path component,
	 * that will be set to '/'.
	 *
	 * @param  string|Url $url Request URL
	 * @return Url|self
	 * @throws InvalidArgumentException
	 */
	public function url($url = NULL)
	{
		if(NULL === $url)
		{
			return $this->url;
		}
		if (is_string($url))
		{
			$url = new ZAP_HTTP_Url(
				$url, array(ZAP_HTTP_Url::OPTION_USE_BRACKETS => $this->config['use_brackets'])
		 	);
		}
		if (!$url instanceof ZAP_HTTP_Url)
		{
			throw new InvalidArgumentException('Parameter is not a valid HTTP URL');
		}
		if ($url->userInfo())
		{
			$username = $url->user();
			$password = $url->password();
			$this->auth(rawurldecode($username), $password ? rawurldecode($password): '');
			$url->userInfo('');
		}
		if ('' == $url->path())
		{
			$url->path('/');
		}
		$this->url = $url;
		return $this;
	}

	/**
	 * Gets or sets the request method
	 *
	 * @param  string $method one of the methods defined in RFC 2616
	 * @return string|self
	 * @throws InvalidArgumentException if the method name is invalid
	 */
	public function method($method = NULL)
	{
		if(NULL === $method)
		{
			return $this->method;
		}
		if (preg_match(self::REGEXP_INVALID_TOKEN, $method))
		{
			throw new InvalidArgumentException("Invalid request method '{$method}'");
		}
		$this->method = $method;
		return $this;
	}

	/**
	 * Get or sets the configuration parameter(s)
	 *
	 * The following parameters are available:
	 * <ul>
	 *   <li> 'adapter'           - adapter to use (string)</li>
	 *   <li> 'connect_timeout'   - Connection timeout in seconds (integer)</li>
	 *   <li> 'timeout'           - Total number of seconds a request can take.
	 *                              Use 0 for no limit, should be greater than
	 *                              'connect_timeout' if set (integer)</li>
	 *   <li> 'use_brackets'      - Whether to append [] to array variable names (bool)</li>
	 *   <li> 'protocol_version'  - HTTP Version to use, '1.0' or '1.1' (string)</li>
	 *   <li> 'buffer_size'       - Buffer size to use for reading and writing (int)</li>
	 *   <li> 'store_body'        - Whether to store response body in response object.
	 *                              Set to false if receiving a huge response and
	 *                              using an Observer to save it (boolean)</li>
	 *   <li> 'proxy_type'        - Proxy type, 'http' or 'socks5' (string)</li>
	 *   <li> 'proxy_host'        - Proxy server host (string)</li>
	 *   <li> 'proxy_port'        - Proxy server port (integer)</li>
	 *   <li> 'proxy_user'        - Proxy auth username (string)</li>
	 *   <li> 'proxy_password'    - Proxy auth password (string)</li>
	 *   <li> 'proxy_auth_scheme' - Proxy auth scheme, one of Request::AUTH_* constants (string)</li>
	 *   <li> 'proxy'             - Shorthand for proxy_* parameters, proxy given as URL,
	 *                              e.g. 'socks5://localhost:1080/' (string)</li>
	 *   <li> 'ssl_verify_peer'   - Whether to verify peer's SSL certificate (bool)</li>
	 *   <li> 'ssl_verify_host'   - Whether to check that Common Name in SSL
	 *                              certificate matches host name (bool)</li>
	 *   <li> 'ssl_cafile'        - Cerificate Authority file to verify the peer
	 *                              with (use with 'ssl_verify_peer') (string)</li>
	 *   <li> 'ssl_capath'        - Directory holding multiple Certificate
	 *                              Authority files (string)</li>
	 *   <li> 'ssl_local_cert'    - Name of a file containing local cerificate (string)</li>
	 *   <li> 'ssl_passphrase'    - Passphrase with which local certificate
	 *                              was encoded (string)</li>
	 *   <li> 'digest_compat_ie'  - Whether to imitate behaviour of MSIE 5 and 6
	 *                              in using URL without query string in digest
	 *                              authentication (boolean)</li>
	 *   <li> 'follow_redirects'  - Whether to automatically follow HTTP Redirects (boolean)</li>
	 *   <li> 'max_redirects'     - Maximum number of redirects to follow (integer)</li>
	 *   <li> 'strict_redirects'  - Whether to keep request method on redirects via status 301 and
	 *                              302 (true, needed for compatibility with RFC 2616)
	 *                              or switch to GET (false, needed for compatibility with most
	 *                              browsers) (boolean)</li>
	 * </ul>
	 *
	 * @param  string|array $name  configuration parameter name or array
	 *                             ('parameter name' => 'parameter value')
	 * @param  mixed        $value parameter value if $name is not an array
	 * @return mix|self
	 * @throws InvalidArgumentException If the parameter is unknown
	 */
	public function config($name = NULL, $value = NULL)
	{
		if(NULL === $name)
		{
			return $this->config;
		}
		else
		{
			if(NULL === $value)
			{
				if (is_array($name))
				{
					foreach ($name as $k => $v)
					{
						$this->config($k, $v);
					}
					return $this;
				}
				if (!array_key_exists($name, $this->config))
				{
					throw new InvalidArgumentException("Unknown configuration parameter '{$name}'");
				}
				return $this->config[$name];
			}
			else
			{
				if ('proxy' == $name)
				{
					$url = new ZAP_HTTP_Url($value);
					$this->config(array(
						'proxy_type'     => $url->scheme(),
						'proxy_host'     => $url->host(),
						'proxy_port'     => $url->port(),
						'proxy_user'     => rawurldecode($url->user()),
						'proxy_password' => rawurldecode($url->password()),
					));
				}
				else
				{
					if (!array_key_exists($name, $this->config))
					{
						throw new InvalidArgumentException("Unknown configuration parameter '{$name}'");
					}
					$this->config[$name] = $value;
				}
				return $this;
			}
		}
	}
    
	/**
	 * Get or sets the autentification data
	 *
	 * @param string $user     user name
	 * @param string $password password
	 * @param string $scheme   authentication scheme
	 * @return mix|self
	 */
	public function auth($user = NULL, $password = NULL, $scheme = self::AUTH_BASIC)
	{
		if(NULL === $user)
		{
			return $this->user;
		}
		else
		{
			if (empty($user))
			{
				$this->auth = NULL;
			}
			else
			{
				$this->auth = array(
					'user'     => (string) $user,
					'password' => (string) $password,
					'scheme'   => $scheme,
				);
			}
			return $this;
		}
	}

	/**
	 * Sets request header(s)
	 *
	 * The first parameter may be either a full header string 'header: value' or
	 * header name. In the former case $value parameter is ignored, in the latter
	 * the header's value will either be set to $value or the header will be
	 * removed if $value is null. The first parameter can also be an array of
	 * headers, in that case method will be called recursively.
	 *
	 * Note that headers are treated case insensitively as per RFC 2616.
	 *
	 * <code>
	 * $req->header('Foo: Bar'); // sets the value of 'Foo' header to 'Bar'
	 * $req->header('FoO', 'Baz'); // sets the value of 'Foo' header to 'Baz'
	 * $req->header(array('foo' => 'Quux')); // sets the value of 'Foo' header to 'Quux'
	 * $req->header('FOO'); // removes 'Foo' header from request
	 * </code>
	 *
	 * @param string|array      $name    header name, header string ('Header: value')
	 *                                   or an array of headers
	 * @param string|array|null $value   header value if $name is not an array,
	 *                                   header will be removed if value is null
	 * @param bool              $replace whether to replace previous header with the
	 *                                   same name or append to its value
	 *
	 * @return   self
	 * @throws   InvalidArgumentException
	 */
	public function header($name, $value = NULL, $replace = TRUE)
	{
		if(NULL === $value)
		{
			if (is_array($name))
			{
				foreach ($name as $k => $v)
				{
					if (is_string($k))
					{
						$this->header($k, $v, $replace);
					}
				}
				return $this;
			}
			elseif (strpos($name, ':'))
			{
				list($name, $value) = array_map('trim', explode(':', $name, 2));
				if(!empty($value))
				{
					$this->header($name, $value, $replace);
				}
				return $this;
			}
			return isset($this->headers[$name]) ? $this->headers[$name] : NULL;
		}
		else
		{
			if (preg_match(self::REGEXP_INVALID_TOKEN, $name))
			{
				throw new InvalidArgumentException("Invalid header name '{$name}'");
			}
			$name = strtolower($name);
			if(FALSE === $value)
			{
				unset($this->headers[$name]);
			}
			elseif (is_array($value))
			{
				$value = implode(', ', array_map('trim', $value));
			}
			elseif (is_string($value))
			{
				$value = trim($value);
			}
			if (!isset($this->headers[$name]) OR $replace)
			{
				$this->headers[$name] = $value;
			}
			else
			{
				$this->headers[$name] .= ', ' . $value;
			}
			return $this;
		}
	}

	/**
	 * Returns the request headers
	 *
	 * The array is of the form ('header name' => 'header value'), header names
	 * are lowercased
	 *
	 * @return   array
	 */
	public function headers()
	{
		return $this->headers;
	}

	/**
	 * Adds a cookie to the request
	 *
	 * If the request does not have a CookieJar object set, this method simply
	 * appends a cookie to "Cookie:" header.
	 *
	 * If a CookieJar object is available, the cookie is stored in that object.
	 * Data from request URL will be used for setting its 'domain' and 'path'
	 * parameters, 'expires' and 'secure' will be set to null and false,
	 * respectively. If you need further control, use CookieJar's methods.
	 *
	 * @param string $name  cookie name
	 * @param string $value cookie value
	 *
	 * @return   self
	 * @throws   InvalidArgumentException
	 * @see      cookieJar()
	 */
	public function addCookie($name, $value)
	{
		if (!empty($this->cookieJar))
		{
			$this->cookieJar->store(
				array('name' => $name, 'value' => $value), $this->url
			);
		}
		else
		{
			$cookie = $name . '=' . $value;
			if (preg_match(self::REGEXP_INVALID_COOKIE, $cookie))
			{
				throw new InvalidArgumentException("Invalid cookie: '{$cookie}'");
			}
			$cookies = empty($this->headers['cookie'])? '': $this->headers['cookie'] . '; ';
			$this->header('cookie', $cookies . $cookie);
		}

		return $this;
	}

	/**
	 * Get or sets the request body
	 *
	 * If you provide file pointer rather than file name, it should support
	 * fstat() and rewind() operations.
	 *
	 * @param string|resource|Multipart $body Either a string with the body or filename containing body or pointer to an open file or object with multipart body data
	 * @param bool $isFilename Whether first parameter is a filename
	 *
	 * @return self
	 */
	public function body($body = NULL, $isFilename = FALSE)
	{
		if(NULL !== $body)
		{
			if (!$isFilename AND !is_resource($body))
			{
				if (!$body instanceof ZAP_HTTP_Multipart)
				{
					$this->body = (string)$body;
				}
				else
				{
					$this->body = $body;
				}
			}
			else
			{
				$fileData = $this->fopenWrapper($body, empty($this->headers['content-type']));
				$this->body = $fileData['fp'];
				if (empty($this->headers['content-type']))
				{
					$this->header('content-type', $fileData['type']);
				}
			}
			$this->postParams = $this->uploads = array();

			return $this;
		}
		else
		{
			if (self::METHOD_POST == $this->method AND (!empty($this->postParams) OR !empty($this->uploads)))
			{
				if (0 === strpos($this->headers['content-type'], 'application/x-www-form-urlencoded'))
				{
					$body = http_build_query($this->postParams, '', '&');
					if (!$this->config('use_brackets'))
					{
						$body = preg_replace('/%5B\d+%5D=/', '=', $body);
					}
					// support RFC 3986 by not encoding '~' symbol (request #15368)
					return str_replace('%7E', '~', $body);

				}
				elseif (0 === strpos($this->headers['content-type'], 'multipart/form-data'))
				{
					return new ZAP_HTTP_Multipart(
						$this->postParams, $this->uploads, $this->config('use_brackets')
					);
				}
			}
			return $this->body;
		}
	}

	/**
	 * Adds a file to form-based file upload
	 *
	 * Used to emulate file upload via a HTML form. The method also sets
	 * Content-Type of HTTP request to 'multipart/form-data'.
	 *
	 * If you just want to send the contents of a file as the body of HTTP
	 * request you should use setBody() method.
	 *
	 * If you provide file pointers rather than file names, they should support
	 * fstat() and rewind() operations.
	 *
	 * @param string                $fieldName    name of file-upload field
	 * @param string|resource|array $filename     full name of local file, pointer to open file or an array of files
	 * @param string                $sendFilename filename to send in the request
	 * @param string                $contentType  content-type of file being uploaded
	 *
	 * @return self
	 */
	public function addUpload($fieldName, $filename, $sendFilename = NULL, $contentType = NULL)
	{
		if (!is_array($filename))
		{
			$fileData = $this->fopenWrapper($filename, empty($contentType));
			$this->uploads[$fieldName] = array(
				'fp'        => $fileData['fp'],
				'filename'  => !empty($sendFilename)? $sendFilename
								:(is_string($filename)? basename($filename): 'anonymous.blob') ,
				'size'      => $fileData['size'],
				'type'      => empty($contentType)? $fileData['type']: $contentType
			);
		}
		else
		{
			$fps = $names = $sizes = $types = array();
			foreach ($filename as $f)
			{
				if (!is_array($f))
				{
					$f = array($f);
				}
				$fileData = $this->fopenWrapper($f[0], empty($f[2]));
				$fps[]   = $fileData['fp'];
				$names[] = !empty($f[1])? $f[1]
							:(is_string($f[0])? basename($f[0]): 'anonymous.blob');
				$sizes[] = $fileData['size'];
				$types[] = empty($f[2])? $fileData['type']: $f[2];
			}
			$this->uploads[$fieldName] = array(
				'fp' => $fps, 'filename' => $names, 'size' => $sizes, 'type' => $types
			);
		}
		if (empty($this->headers['content-type'])
			OR 'application/x-www-form-urlencoded' == $this->headers['content-type']
		)
		{
			$this->header('content-type', 'multipart/form-data');
		}

		return $this;
	}

	/**
	 * Adds POST parameter(s) to the request.
	 *
	 * @param string|array $name  parameter name or array ('name' => 'value')
	 * @param mixed        $value parameter value (can be an array)
	 *
	 * @return self
	 */
	public function addPostParameter($name, $value = NULL)
	{
		if (!is_array($name))
		{
			$this->postParams[$name] = $value;
		}
		else
		{
			foreach ($name as $k => $v)
			{
				$this->addPostParameter($k, $v);
			}
		}
		if (empty($this->headers['content-type']))
		{
			$this->header('content-type', 'application/x-www-form-urlencoded');
		}

		return $this;
	}

	/**
	 * Sets the adapter used to actually perform the request
	 *
	 * You can pass either an instance of a class implementing Adapter
	 * or a class name. The method will only try to include a file if the class
	 * name starts with Adapter_, it will also try to prepend this
	 * prefix to the class name if it doesn't contain any underscores, so that
	 * <code>
	 * $request->adapter('curl');
	 * </code>
	 * will work.
	 *
	 * @param string|Adapter $adapter Adapter to use
	 *
	 * @return   self
	 */
	public function adapter($adapter = NULL)
	{
		if(NULL === $adapter)
		{
			return $this->adapter;
		}
		if (is_string($adapter))
		{
			if (!class_exists($adapter, FALSE))
			{
				if (FALSE === strpos($adapter, '_'))
				{
					$adapter = 'ZAP_HTTP_Adapter_' . ucfirst($adapter);
				}
				if (!class_exists($adapter, TRUE))
				{
					throw new LogicException("Class {$adapter} not found");
				}
			}
			$adapter = new $adapter;
		}
		if (!$adapter instanceof ZAP_HTTP_Adapter)
		{
			throw new InvalidArgumentException('Parameter is not a HTTP request adapter');
		}
		$this->adapter = $adapter;

		return $this;
	}

	/**
	 * Sets the cookie jar
	 *
	 * A cookie jar is used to maintain cookies across HTTP requests and
	 * responses. Cookies from jar will be automatically added to the request
	 * headers based on request URL.
	 *
	 * @param CookieJar|bool $jar Existing CookieJar object, true to
	 *                                          create a new one, false to remove
	 *
	 * @return CookieJar|self
	 * @throws InvalidArgumentException
	 */
	public function cookieJar($jar = NULL)
	{
		if(NULL === $jar)
		{
			return $this->cookieJar;
		}
		else
		{
			if ($jar instanceof ZAP_HTTP_CookieJar)
			{
				$this->cookieJar = $jar;
			}
			elseif (TRUE === $jar)
			{
				$this->cookieJar = new ZAP_HTTP_CookieJar;
			}
			elseif (FALSE === $jar)
			{
				$this->cookieJar = NULL;
			}
			else
			{
				throw new InvalidArgumentException('Invalid parameter passed to cookieJar()');
			}

			return $this;
		}
	}

	/**
	 * Sends the request and returns the response
	 *
	 * @throws Exception
	 * @return Response
	 */
	public function send()
	{
		// Sanity check for URL
		if (!$this->url instanceof ZAP_HTTP_Url
			OR !$this->url->isAbsolute()
			OR !in_array(strtolower($this->url->scheme()), array('https', 'http'))
		)
		{
			throw new InvalidArgumentException(
				'Request needs an absolute HTTP(S) request URL, '
				. ($this->url instanceof ZAP_HTTP_Url
				   ? "'" . $this->url->__toString() . "'" : 'none')
				. ' given'
			);
		}
		if (empty($this->adapter))
		{
			$this->adapter($this->config('adapter'));
		}
		// magic_quotes_runtime may break file uploads and chunked response
		// processing; see bug #4543. Don't use ini_get() here; see bug #16440.
		if ($magicQuotes = get_magic_quotes_runtime())
		{
			set_magic_quotes_runtime(FALSE);
		}
		// force using single byte encoding if mbstring extension overloads
		// strlen() and substr(); see bug #1781, bug #10605
		if (extension_loaded('mbstring') AND (2 & ini_get('mbstring.func_overload')))
		{
			$oldEncoding = mb_internal_encoding();
			mb_internal_encoding('8bit');
		}

		try
		{
			$response = $this->adapter->sendRequest($this);
		}
		catch (Exception $e)
		{
		}
		// cleanup in either case (poor man's "finally" clause)
		if ($magicQuotes)
		{
			set_magic_quotes_runtime(TRUE);
		}
		if (!empty($oldEncoding))
		{
			mb_internal_encoding($oldEncoding);
		}
		// rethrow the exception
		if (!empty($e))
		{
			throw $e;
		}
		return $response;
	}

	/**
	 * Wrapper around fopen()/fstat() used by setBody() and addUpload()
	 *
	 * @param string|resource $file       file name or pointer to open file
	 * @param bool            $detectType whether to try autodetecting MIME
	 *                        type of file, will only work if $file is a
	 *                        filename, not pointer
	 *
	 * @return array array('fp' => file pointer, 'size' => file size, 'type' => MIME type)
	 * @throws LogicException
	 */
	protected function fopenWrapper($file, $detectType = FALSE)
	{
		if (!is_string($file) AND !is_resource($file))
		{
			throw new InvalidArgumentException("Filename or file pointer resource expected");
		}
		$fileData = array(
			'fp'   => is_string($file)? NULL: $file,
			'type' => 'application/octet-stream',
			'size' => 0
		);
		if (is_string($file))
		{
			if (!($fileData['fp'] = @fopen($file, 'rb')))
			{
				$error = error_get_last();
				throw new LogicException($error['message']);
			}
			if ($detectType)
			{
				$fileData['type'] = self::detectMimeType($file);
			}
		}
		if (!($stat = fstat($fileData['fp'])))
		{
			throw new LogicException("fstat() call failed");
		}
		$fileData['size'] = $stat['size'];

		return $fileData;
	}

	/**
	 * Tries to detect MIME type of a file
	 *
	 * The method will try to use fileinfo extension if it is available,
	 * deprecated mime_content_type() function in the other case. If neither
	 * works, default 'application/octet-stream' MIME type is returned
	 *
	 * @param string $filename file name
	 *
	 * @return   string  file MIME type
	 */
	protected static function detectMimeType($filename)
	{
		// finfo extension from PECL available
		if (function_exists('finfo_open'))
		{
			if (!isset(self::$_fileinfoDb))
			{
				self::$_fileinfoDb = @finfo_open(FILEINFO_MIME);
			}
			if (self::$_fileinfoDb)
			{
				$info = finfo_file(self::$_fileinfoDb, $filename);
			}
		}
		// (deprecated) mime_content_type function available
		if (empty($info) AND function_exists('mime_content_type'))
		{
			return mime_content_type($filename);
		}
		return empty($info)? 'application/octet-stream': $info;
	}
}

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
 * Socket-based adapter for Request
 *
 * This adapter uses only PHP sockets and will work on almost any PHP
 * environment. Code is based on original HTTP_Request PEAR package.
 * 
 * @package  Zimbra
 * @category Http
 * @author   Alexey Borzov <avb@php.net>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 */
class ZAP_HTTP_Adapter_Socket extends ZAP_HTTP_Adapter
{
	/**
	 * Regular expression for 'token' rule from RFC 2616
	 */
	const REGEXP_TOKEN = '[^\x00-\x1f\x7f-\xff()<>@,;:\\\\"/\[\]?={}\s]+';

	/**
	 * Regular expression for 'quoted-string' rule from RFC 2616
	 */
	const REGEXP_QUOTED_STRING = '"(?:\\\\.|[^\\\\"])*"';

	/**
	 * Connected sockets, needed for Keep-Alive support
	 * @var  array
	 * @see  connect()
	 */
	protected static $sockets = array();

	/**
	 * Data for digest authentication scheme
	 *
	 * The keys for the array are URL prefixes.
	 *
	 * The values are associative arrays with data (realm, nonce, nonce-count,
	 * opaque...) needed for digest authentication. Stored here to prevent making
	 * duplicate requests to digest-protected resources after we have already
	 * received the challenge.
	 *
	 * @var  array
	 */
	protected static $challenges = array();

	/**
	 * Connected socket
	 * @var  Request_SocketWrapper
	 * @see  connect()
	 */
	protected $socket;

	/**
	 * Challenge used for server digest authentication
	 * @var  array
	 */
	protected $serverChallenge;

	/**
	 * Challenge used for proxy digest authentication
	 * @var  array
	 */
	protected $proxyChallenge;

	/**
	 * Remaining length of the current chunk, when reading chunked response
	 * @var  integer
	 * @see  readChunked()
	 */
	protected $chunkLength = 0;

	/**
	 * Remaining amount of redirections to follow
	 *
	 * Starts at 'max_redirects' configuration parameter and is reduced on each
	 * subsequent redirect. An Exception will be thrown once it reaches zero.
	 *
	 * @var  integer
	 */
	protected $redirectCountdown = NULL;

	/**
	 * Sends request to the remote server and returns its response
	 *
	 * @param Request $request HTTP request message
	 *
	 * @return Request
	 * @throws Exception
	 */
	public function sendRequest(ZAP_HTTP_Request $request)
	{
		$this->request = $request;

		try
		{
			$keepAlive = $this->connect();
			$headers   = $this->prepareHeaders();
			$this->socket->write($headers);
			$this->writeBody();

			$response = $this->readResponse();

			if ($jar = $request->cookieJar())
			{
				$jar->addCookiesFromResponse($response, $request->url());
			}

			if (!$this->canKeepAlive($keepAlive, $response))
			{
				$this->disconnect();
			}

			if ($this->shouldUseProxyDigestAuth($response))
			{
				return $this->sendRequest($request);
			}
			if ($this->shouldUseServerDigestAuth($response))
			{
				return $this->sendRequest($request);
			}
			if ($authInfo = $response->header('authentication-info'))
			{
				$this->updateChallenge($this->serverChallenge, $authInfo);
			}
			if ($proxyInfo = $response->header('proxy-authentication-info'))
			{
				$this->updateChallenge($this->proxyChallenge, $proxyInfo);
			}

		}
		catch (Exception $e)
		{
			$this->disconnect();
		}

		unset($this->request, $this->requestBody);

		if (!empty($e))
		{
			$this->redirectCountdown = NULL;
			throw $e;
		}

		if (!$request->config('follow_redirects') OR !$response->isRedirect())
		{
			$this->redirectCountdown = NULL;
			return $response;
		}
		else
		{
			return $this->handleRedirect($request, $response);
		}
	}

	/**
	 * Connects to the remote server
	 *
	 * @return   bool    whether the connection can be persistent
	 * @throws   Request_Exception
	 */
	protected function connect()
	{
		$secure  = 0 == strcasecmp($this->request->url()->scheme(), 'https');
		$tunnel  = ZAP_HTTP_Request::METHOD_CONNECT == $this->request->method();
		$headers = $this->request->headers();
		$reqHost = $this->request->url()->host();
		if (!($reqPort = $this->request->url()->port()))
		{
			$reqPort = $secure? 443: 80;
		}

		$httpProxy = $socksProxy = FALSE;
		if (!($host = $this->request->config('proxy_host')))
		{
			$host = $reqHost;
			$port = $reqPort;
		}
		else
		{
			if (!($port = $this->request->config('proxy_port')))
			{
				throw new LogicException('Proxy port not provided');
			}
			if ('http' == ($type = $this->request->config('proxy_type')))
			{
				$httpProxy = TRUE;
			}
			elseif ('socks5' == $type)
			{
				$socksProxy = TRUE;
			}
			else
			{
				throw new LogicException("Proxy type '{$type}' is not supported");
			}
		}

		if ($tunnel AND !$httpProxy)
		{
			throw new LogicException("Trying to perform CONNECT request without proxy");
		}
		if ($secure AND !in_array('ssl', stream_get_transports()))
		{
			throw new LogicException('Need OpenSSL support for https:// requests');
		}

		// RFC 2068, section 19.7.1: A client MUST NOT send the Keep-Alive
		// connection token to a proxy server...
		if ($httpProxy AND !$secure AND !empty($headers['connection']) AND 'Keep-Alive' == $headers['connection'])
		{
			$this->request->header('connection', FALSE);
		}

		$keepAlive = ('1.1' == $this->request->config('protocol_version') AND
					  empty($headers['connection'])) OR
					 (!empty($headers['connection']) AND
					  'Keep-Alive' == $headers['connection']);

		$options = array();
		if ($secure OR $tunnel)
		{
			foreach ($this->request->config() as $name => $value)
			{
				if ('ssl_' == substr($name, 0, 4) AND NULL !== $value)
				{
					if ('ssl_verify_host' == $name)
					{
						if ($value)
						{
							$options['CN_match'] = $reqHost;
						}
					}
					else
					{
						$options[substr($name, 4)] = $value;
					}
				}
			}
			ksort($options);
		}

		// Use global request timeout if given, see feature requests #5735, #8964
		if ($timeout = $this->request->config('timeout'))
		{
			$deadline = time() + $timeout;
		}
		else
		{
			$deadline = NULL;
		}

		// Changing SSL context options after connection is established does *not*
		// work, we need a new connection if options change
		$remote    = ((!$secure OR $httpProxy OR $socksProxy)? 'tcp://': 'ssl://')
					 . $host . ':' . $port;
		$socketKey = $remote . (
						($secure AND $httpProxy OR $socksProxy)
						? "->{$reqHost}:{$reqPort}" : ''
					 ) . (empty($options)? '': ':' . serialize($options));
		unset($this->socket);

		// We use persistent connections and have a connected socket?
		// Ensure that the socket is still connected, see bug #16149
		if ($keepAlive AND !empty(self::$sockets[$socketKey]) AND !self::$sockets[$socketKey]->eof())
		{
			$this->socket =& self::$sockets[$socketKey];
		}
		else
		{
			if ($socksProxy)
			{
				$this->socket = new ZAP_HTTP_Wrapper_SOCKS5(
					$remote, $this->request->config('connect_timeout'),
					$options, $this->request->config('proxy_user'),
					$this->request->config('proxy_password')
				);
				// handle request timeouts ASAP
				$this->socket->deadline($deadline, $this->request->config('timeout'));
				$this->socket->connect($reqHost, $reqPort);
				if (!$secure)
				{
					$conninfo = "tcp://{$reqHost}:{$reqPort} via {$remote}";
				}
				else
				{
					$this->socket->enableCrypto();
					$conninfo = "ssl://{$reqHost}:{$reqPort} via {$remote}";
				}

			}
			elseif ($secure AND $httpProxy AND !$tunnel)
			{
				$this->establishTunnel();
				$conninfo = "ssl://{$reqHost}:{$reqPort} via {$remote}";
			}
			else
			{
				$this->socket = new ZAP_HTTP_Wrapper_SocketWrapper(
					$remote, $this->request->config('connect_timeout'), $options
				);
			}
			self::$sockets[$socketKey] =& $this->socket;
		}
		$this->socket->deadline($deadline, $this->request->config('timeout'));
		return $keepAlive;
	}

	/**
	 * Establishes a tunnel to a secure remote server via HTTP CONNECT request
	 *
	 * This method will fail if 'ssl_verify_peer' is enabled. Probably because PHP
	 * sees that we are connected to a proxy server (duh!) rather than the server
	 * that presents its certificate.
	 *
	 * @link     http://tools.ietf.org/html/rfc2817#section-5.2
	 * @throws   Request_Exception
	 */
	protected function establishTunnel()
	{
		$donor   = new self;
		$connect = new ZAP_HTTP_Request(
			$this->request->url(), ZAP_HTTP_Request::METHOD_CONNECT,
			array_merge($this->request->config(), array('adapter' => $donor))
		);
		$response = $connect->send();
		// Need any successful (2XX) response
		if (200 > $response->status() OR 300 <= $response->status())
		{
			throw new RuntimeException(
				'Failed to connect via HTTPS proxy. Proxy response: ' .
				$response->status() . ' ' . $response->reasonPhrase()
			);
		}
		$this->socket = $donor->socket;
		$this->socket->enableCrypto();
	}

	/**
	 * Checks whether current connection may be reused or should be closed
	 *
	 * @param boolean  $requestKeepAlive whether connection could
	 *                                   be persistent in the first place
	 * @param Response $response         response object to check
	 *
	 * @return boolean
	 */
	protected function canKeepAlive($requestKeepAlive, ZAP_HTTP_Response $response)
	{
		// Do not close socket on successful CONNECT request
		if (ZAP_HTTP_Request::METHOD_CONNECT == $this->request->method() AND 200 <= $response->status() AND 300 > $response->status())
		{
			return TRUE;
		}

		$lengthKnown = 'chunked' == strtolower($response->header('transfer-encoding'))
					   OR NULL !== $response->header('content-length')
					   // no body possible for such responses, see also request #17031
					   OR ZAP_HTTP_Request::METHOD_HEAD == $this->request->method()
					   OR in_array($response->status(), array(204, 304));
		$persistent  = 'keep-alive' == strtolower($response->header('connection')) OR
					   (NULL === $response->header('connection') AND
						'1.1' == $response->version());
		return $requestKeepAlive AND $lengthKnown AND $persistent;
	}

	/**
	 * Disconnects from the remote server
	 */
	protected function disconnect()
	{
		if (!empty($this->socket))
		{
			$this->socket = NULL;
		}
	}

	/**
	 * Handles HTTP redirection
	 *
	 * This method will throw an Exception if redirect to a non-HTTP(S) location
	 * is attempted, also if number of redirects performed already is equal to
	 * 'max_redirects' configuration parameter.
	 *
	 * @param Request  $request  Original request
	 * @param Response $response Response containing redirect
	 *
	 * @return Response      Response from a new location
	 * @throws Exception
	 */
	protected function handleRedirect(ZAP_HTTP_Request $request, ZAP_HTTP_Response $response)
	{
		if (is_null($this->redirectCountdown))
		{
			$this->redirectCountdown = $request->config('max_redirects');
		}
		if (0 == $this->redirectCountdown)
		{
			$this->redirectCountdown = NULL;
			// Copying cURL behaviour
			throw new LogicException(
				'Maximum (' . $request->config('max_redirects') . ') redirects followed'
			);
		}
		$redirectUrl = new ZAP_HTTP_Url(
			$response->header('location'),
			array(ZAP_HTTP_Url::OPTION_USE_BRACKETS => $request->config('use_brackets'))
		);
		// refuse non-HTTP redirect
		if ($redirectUrl->isAbsolute()
			AND !in_array($redirectUrl->scheme(), array('http', 'https'))
		)
		{
			$this->redirectCountdown = NULL;
			throw new LogicException(
				'Refusing to redirect to a non-HTTP URL ' . $redirectUrl->__toString()
			);
		}
		// Theoretically URL should be absolute (see http://tools.ietf.org/html/rfc2616#section-14.30),
		// but in practice it is often not
		if (!$redirectUrl->isAbsolute())
		{
			$redirectUrl = $request->url()->resolve($redirectUrl);
		}
		$redirect = clone $request;
		$redirect->url($redirectUrl);
		if (303 == $response->status()
			OR (!$request->config('strict_redirects')
				AND in_array($response->status(), array(301, 302)))
		)
		{
			$redirect->method(ZAP_HTTP_Request::METHOD_GET);
			$redirect->body('');
		}

		if (0 < $this->redirectCountdown)
		{
			$this->redirectCountdown--;
		}
		return $this->sendRequest($redirect);
	}

	/**
	 * Checks whether another request should be performed with server digest auth
	 *
	 * Several conditions should be satisfied for it to return true:
	 *   - response status should be 401
	 *   - auth credentials should be set in the request object
	 *   - response should contain WWW-Authenticate header with digest challenge
	 *   - there is either no challenge stored for this URL or new challenge
	 *     contains stale=true parameter (in other case we probably just failed
	 *     due to invalid username / password)
	 *
	 * The method stores challenge values in $challenges static property
	 *
	 * @param Response $response response to check
	 *
	 * @return boolean whether another request should be performed
	 * @throws Exception in case of unsupported challenge parameters
	 */
	protected function shouldUseServerDigestAuth(ZAP_HTTP_Response $response)
	{
		// no sense repeating a request if we don't have credentials
		if (401 != $response->status() OR !$this->request->auth()) {
			return FALSE;
		}
		if (!$challenge = $this->parseDigestChallenge($response->header('www-authenticate')))
		{
			return FALSE;
		}

		$url    = $this->request->url();
		$scheme = $url->scheme();
		$host   = $scheme . '://' . $url->host();
		if ($port = $url->port())
		{
			if ((0 == strcasecmp($scheme, 'http') AND 80 != $port)
				OR (0 == strcasecmp($scheme, 'https') AND 443 != $port)
			)
			{
				$host .= ':' . $port;
			}
		}

		if (!empty($challenge['domain']))
		{
			$prefixes = array();
			foreach (preg_split('/\\s+/', $challenge['domain']) as $prefix)
			{
				// don't bother with different servers
				if ('/' == substr($prefix, 0, 1))
				{
					$prefixes[] = $host . $prefix;
				}
			}
		}
		if (empty($prefixes))
		{
			$prefixes = array($host . '/');
		}

		$ret = TRUE;
		foreach ($prefixes as $prefix) {
			if (!empty(self::$challenges[$prefix])
				AND (empty($challenge['stale']) OR strcasecmp('true', $challenge['stale']))
			)
			{
				// probably credentials are invalid
				$ret = FALSE;
			}
			self::$challenges[$prefix] =& $challenge;
		}
		return $ret;
	}

	/**
	 * Checks whether another request should be performed with proxy digest auth
	 *
	 * Several conditions should be satisfied for it to return true:
	 *   - response status should be 407
	 *   - proxy auth credentials should be set in the request object
	 *   - response should contain Proxy-Authenticate header with digest challenge
	 *   - there is either no challenge stored for this proxy or new challenge
	 *     contains stale=true parameter (in other case we probably just failed
	 *     due to invalid username / password)
	 *
	 * The method stores challenge values in $challenges static property
	 *
	 * @param Response $response response to check
	 *
	 * @return boolean whether another request should be performed
	 * @throws Exception in case of unsupported challenge parameters
	 */
	protected function shouldUseProxyDigestAuth(ZAP_HTTP_Response $response)
	{
		if (407 != $response->status() OR !$this->request->config('proxy_user'))
		{
			return FALSE;
		}
		if (!($challenge = $this->parseDigestChallenge($response->header('proxy-authenticate'))))
		{
			return FALSE;
		}

		$key = 'proxy://' . $this->request->config('proxy_host') .
			   ':' . $this->request->config('proxy_port');

		if (!empty(self::$challenges[$key])
			AND (empty($challenge['stale']) OR strcasecmp('true', $challenge['stale']))
		)
		{
			$ret = FALSE;
		} else {
			$ret = TRUE;
		}
		self::$challenges[$key] = $challenge;
		return $ret;
	}

	/**
	 * Extracts digest method challenge from (WWW|Proxy)-Authenticate header value
	 *
	 * There is a problem with implementation of RFC 2617: several of the parameters
	 * are defined as quoted-string there and thus may contain backslash escaped
	 * double quotes (RFC 2616, section 2.2). However, RFC 2617 defines unq(X) as
	 * just value of quoted-string X without surrounding quotes, it doesn't speak
	 * about removing backslash escaping.
	 *
	 * Now realm parameter is user-defined and human-readable, strange things
	 * happen when it contains quotes:
	 *   - Apache allows quotes in realm, but apparently uses realm value without
	 *     backslashes for digest computation
	 *   - Squid allows (manually escaped) quotes there, but it is impossible to
	 *     authorize with either escaped or unescaped quotes used in digest,
	 *     probably it can't parse the response (?)
	 *   - Both IE and Firefox display realm value with backslashes in
	 *     the password popup and apparently use the same value for digest
	 *
	 * Request follows IE and Firefox (and hopefully RFC 2617) in
	 * quoted-string handling, unfortunately that means failure to authorize
	 * sometimes
	 *
	 * @param string $headerValue value of WWW-Authenticate or Proxy-Authenticate header
	 *
	 * @return mixed   associative array with challenge parameters, false if
	 *                   no challenge is present in header value
	 * @throws LogicException in case of unsupported challenge parameters
	 */
	protected function parseDigestChallenge($headerValue)
	{
		$authParam   = '(' . self::REGEXP_TOKEN . ')\\s*=\\s*(' .
					   self::REGEXP_TOKEN . '|' . self::REGEXP_QUOTED_STRING . ')';
		$challenge   = "!(?<=^|\\s|,)Digest ({$authParam}\\s*(,\\s*|$))+!";
		if (!preg_match($challenge, $headerValue, $matches))
		{
			return FALSE;
		}

		preg_match_all('!' . $authParam . '!', $matches[0], $params);
		$paramsAry   = array();
		$knownParams = array('realm', 'domain', 'nonce', 'opaque', 'stale',
							 'algorithm', 'qop');
		for ($i = 0; $i < count($params[0]); $i++)
		{
			// section 3.2.1: Any unrecognized directive MUST be ignored.
			if (in_array($params[1][$i], $knownParams))
			{
				if ('"' == substr($params[2][$i], 0, 1))
				{
					$paramsAry[$params[1][$i]] = substr($params[2][$i], 1, -1);
				}
				else
				{
					$paramsAry[$params[1][$i]] = $params[2][$i];
				}
			}
		}
		// we only support qop=auth
		if (!empty($paramsAry['qop'])
			AND !in_array('auth', array_map('trim', explode(',', $paramsAry['qop'])))
		)
		{
			throw new LogicException(
				"Only 'auth' qop is currently supported in digest authentication, " .
				"server requested '{$paramsAry['qop']}'"
			);
		}
		// we only support algorithm=MD5
		if (!empty($paramsAry['algorithm']) AND 'MD5' != $paramsAry['algorithm'])
		{
			throw new LogicException(
				"Only 'MD5' algorithm is currently supported in digest authentication, " .
				"server requested '{$paramsAry['algorithm']}'"
			);
		}

		return $paramsAry;
	}

	/**
	 * Parses [Proxy-]Authentication-Info header value and updates challenge
	 *
	 * @param array  &$challenge  challenge to update
	 * @param string $headerValue value of [Proxy-]Authentication-Info header
	 *
	 * @todo     validate server rspauth response
	 */
	protected function updateChallenge(&$challenge, $headerValue)
	{
		$authParam   = '!(' . self::REGEXP_TOKEN . ')\\s*=\\s*(' .
					   self::REGEXP_TOKEN . '|' . self::REGEXP_QUOTED_STRING . ')!';
		$paramsAry   = array();

		preg_match_all($authParam, $headerValue, $params);
		for ($i = 0; $i < count($params[0]); $i++)
		{
			if ('"' == substr($params[2][$i], 0, 1))
			{
				$paramsAry[$params[1][$i]] = substr($params[2][$i], 1, -1);
			}
			else
			{
				$paramsAry[$params[1][$i]] = $params[2][$i];
			}
		}
		// for now, just update the nonce value
		if (!empty($paramsAry['nextnonce']))
		{
			$challenge['nonce'] = $paramsAry['nextnonce'];
			$challenge['nc']    = 1;
		}
	}

	/**
	 * Creates a value for [Proxy-]Authorization header when using digest authentication
	 *
	 * @param string $user       user name
	 * @param string $password   password
	 * @param string $url        request URL
	 * @param array  &$challenge digest challenge parameters
	 *
	 * @return   string  value of [Proxy-]Authorization request header
	 * @link     http://tools.ietf.org/html/rfc2617#section-3.2.2
	 */
	protected function createDigestResponse($user, $password, $url, &$challenge)
	{
		if (FALSE !== ($q = strpos($url, '?'))
			AND $this->request->config('digest_compat_ie')
		)
		{
			$url = substr($url, 0, $q);
		}

		$a1 = md5($user . ':' . $challenge['realm'] . ':' . $password);
		$a2 = md5($this->request->method() . ':' . $url);

		if (empty($challenge['qop']))
		{
			$digest = md5($a1 . ':' . $challenge['nonce'] . ':' . $a2);
		}
		else
		{
			$challenge['cnonce'] = 'Req2.' . rand();
			if (empty($challenge['nc'])) {
				$challenge['nc'] = 1;
			}
			$nc     = sprintf('%08x', $challenge['nc']++);
			$digest = md5(
				$a1 . ':' . $challenge['nonce'] . ':' . $nc . ':' .
				$challenge['cnonce'] . ':auth:' . $a2
			);
		}
		return 'Digest username="' . str_replace(array('\\', '"'), array('\\\\', '\\"'), $user) . '", ' .
			   'realm="' . $challenge['realm'] . '", ' .
			   'nonce="' . $challenge['nonce'] . '", ' .
			   'uri="' . $url . '", ' .
			   'response="' . $digest . '"' .
			   (!empty($challenge['opaque'])?
				', opaque="' . $challenge['opaque'] . '"':
				'') .
			   (!empty($challenge['qop'])?
				', qop="auth", nc=' . $nc . ', cnonce="' . $challenge['cnonce'] . '"':
				'');
	}

	/**
	 * Adds 'Authorization' header (if needed) to request headers array
	 *
	 * @param array  &$headers    request headers
	 * @param string $requestHost request host (needed for digest authentication)
	 * @param string $requestUrl  request URL (needed for digest authentication)
	 *
	 * @throws LogicException
	 */
	protected function addAuthorizationHeader(&$headers, $requestHost, $requestUrl)
	{
		if (!($auth = $this->request->auth()))
		{
			return;
		}
		switch ($auth['scheme']) {
		case ZAP_HTTP_Request::AUTH_BASIC:
			$headers['authorization'] = 'Basic ' . base64_encode(
				$auth['user'] . ':' . $auth['password']
			);
			break;

		case ZAP_HTTP_Request::AUTH_DIGEST:
			unset($this->serverChallenge);
			$fullUrl = ('/' == $requestUrl[0])?
					   $this->request->url()->scheme() . '://' .
					   $requestHost . $requestUrl:
					   $requestUrl;
			foreach (array_keys(self::$challenges) as $key)
			{
				if ($key == substr($fullUrl, 0, strlen($key)))
				{
					$headers['authorization'] = $this->createDigestResponse(
						$auth['user'], $auth['password'],
						$requestUrl, self::$challenges[$key]
					);
					$this->serverChallenge =& self::$challenges[$key];
					break;
				}
			}
			break;

		default:
			throw new LogicException(
				"Unknown HTTP authentication scheme '{$auth['scheme']}'"
			);
		}
	}

	/**
	 * Adds 'Proxy-Authorization' header (if needed) to request headers array
	 *
	 * @param array  &$headers   request headers
	 * @param string $requestUrl request URL (needed for digest authentication)
	 *
	 * @throws   Request_NotImplementedException
	 */
	protected function addProxyAuthorizationHeader(&$headers, $requestUrl)
	{
		if (!$this->request->config('proxy_host')
			OR !($user = $this->request->config('proxy_user'))
			OR (0 == strcasecmp('https', $this->request->url()->scheme())
				AND ZAP_HTTP_Request::METHOD_CONNECT != $this->request->method())
		)
		{
			return;
		}

		$password = $this->request->config('proxy_password');
		switch ($this->request->config('proxy_auth_scheme'))
		{
			case ZAP_HTTP_Request::AUTH_BASIC:
				$headers['proxy-authorization'] = 'Basic ' . base64_encode(
					$user . ':' . $password
				);
				break;

			case ZAP_HTTP_Request::AUTH_DIGEST:
				unset($this->proxyChallenge);
				$proxyUrl = 'proxy://' . $this->request->config('proxy_host') .
							':' . $this->request->config('proxy_port');
				if (!empty(self::$challenges[$proxyUrl]))
				{
					$headers['proxy-authorization'] = $this->createDigestResponse(
						$user, $password,
						$requestUrl, self::$challenges[$proxyUrl]
					);
					$this->proxyChallenge =& self::$challenges[$proxyUrl];
				}
				break;

			default:
				throw new LogicException(
					"Unknown HTTP authentication scheme '" .
					$this->request->config('proxy_auth_scheme') . "'"
				);
			}
	}


	/**
	 * Creates the string with the Request-Line and request headers
	 *
	 * @return   string
	 * @throws   Request_Exception
	 */
	protected function prepareHeaders()
	{
		$headers = $this->request->headers();
		$url     = $this->request->url();
		$connect = ZAP_HTTP_Request::METHOD_CONNECT == $this->request->method();
		$host    = $url->host();

		$defaultPort = 0 == strcasecmp($url->scheme(), 'https')? 443: 80;
		if (($port = $url->port()) AND $port != $defaultPort OR $connect)
		{
			$host .= ':' . (empty($port)? $defaultPort: $port);
		}
		// Do not overwrite explicitly set 'Host' header, see bug #16146
		if (!isset($headers['host']))
		{
			$headers['host'] = $host;
		}

		if ($connect)
		{
			$requestUrl = $host;
		}
		else
		{
			if (!$this->request->config('proxy_host')
				OR 'http' != $this->request->config('proxy_type')
				OR 0 == strcasecmp($url->scheme(), 'https')
			)
			{
				$requestUrl = '';
			}
			else
			{
				$requestUrl = $url->scheme() . '://' . $host;
			}
			$path        = $url->path();
			$query       = $url->query();
			$requestUrl .= (empty($path)? '/': $path) . (empty($query)? '': '?' . $query);
		}

		if ('1.1' == $this->request->config('protocol_version') AND extension_loaded('zlib') AND !isset($headers['accept-encoding']))
		{
			$headers['accept-encoding'] = 'gzip, deflate';
		}
		if (($jar = $this->request->cookieJar()) AND ($cookies = $jar->matching($this->request->url(), TRUE)))
		{
			$headers['cookie'] = (empty($headers['cookie'])? '': $headers['cookie'] . '; ') . $cookies;
		}

		$this->addAuthorizationHeader($headers, $host, $requestUrl);
		$this->addProxyAuthorizationHeader($headers, $requestUrl);
		$this->calculateRequestLength($headers);

		$headersStr = $this->request->method() . ' ' . $requestUrl . ' HTTP/' .
					  $this->request->config('protocol_version') . "\r\n";
		foreach ($headers as $name => $value)
		{
			$canonicalName = implode('-', array_map('ucfirst', explode('-', $name)));
			$headersStr   .= $canonicalName . ': ' . $value . "\r\n";
		}
		return $headersStr . "\r\n";
	}

	/**
	 * Sends the request body
	 *
	 * @throws Exception
	 */
	protected function writeBody()
	{
		if (in_array($this->request->method(), self::$bodyDisallowed) OR 0 == $this->contentLength)
		{
			return;
		}

		$position   = 0;
		$bufferSize = $this->request->config('buffer_size');
		while ($position < $this->contentLength)
		{
			if (is_string($this->requestBody))
			{
				$str = substr($this->requestBody, $position, $bufferSize);
			}
			elseif (is_resource($this->requestBody))
			{
				$str = fread($this->requestBody, $bufferSize);
			}
			else
			{
				$str = $this->requestBody->read($bufferSize);
			}
			$this->socket->write($str);
			// Provide the length of written string to the observer, request #7630
			$position += strlen($str);
		}
	}

	/**
	 * Reads the remote server's response
	 *
	 * @return Response
	 * @throws Exception
	 */
	protected function readResponse()
	{
		$bufferSize = $this->request->config('buffer_size');

		do
		{
			$response = new ZAP_HTTP_Response(
				$this->socket->readLine($bufferSize), TRUE, $this->request->url()
			);
			do
			{
				$headerLine = $this->socket->readLine($bufferSize);
				$response->parseHeaderLine($headerLine);
			} while ('' != $headerLine);
		} while (in_array($response->status(), array(100, 101)));

		// No body possible in such responses
		if (ZAP_HTTP_Request::METHOD_HEAD == $this->request->method()
			OR (ZAP_HTTP_Request::METHOD_CONNECT == $this->request->method()
				AND 200 <= $response->status() AND 300 > $response->status())
			OR in_array($response->status(), array(204, 304))
		)
		{
			return $response;
		}

		$chunked = 'chunked' == $response->header('transfer-encoding');
		$length  = $response->header('content-length');
		$hasBody = FALSE;
		if ($chunked OR NULL === $length OR 0 < intval($length))
		{
			// RFC 2616, section 4.4:
			// 3. ... If a message is received with both a
			// Transfer-Encoding header field and a Content-Length header field,
			// the latter MUST be ignored.
			$toRead = ($chunked OR NULL === $length)? NULL: $length;
			$this->chunkLength = 0;

			while (!$this->socket->eof() AND (is_null($toRead) OR 0 < $toRead))
			{
				if ($chunked)
				{
					$data = $this->readChunked($bufferSize);
				}
				elseif (is_null($toRead))
				{
					$data = $this->socket->read($bufferSize);
				}
				else
				{
					$data    = $this->socket->read(min($toRead, $bufferSize));
					$toRead -= strlen($data);
				}
				if ('' == $data AND (!$this->chunkLength OR $this->socket->eof()))
				{
					break;
				}

				$hasBody = TRUE;
				if ($this->request->config('store_body'))
				{
					$response->appendBody($data);
				}
			}
		}
		return $response;
	}

	/**
	 * Reads a part of response body encoded with chunked Transfer-Encoding
	 *
	 * @param int $bufferSize buffer size to use for reading
	 *
	 * @return string
	 * @throws Exception
	 */
	protected function readChunked($bufferSize)
	{
		// at start of the next chunk?
		if (0 == $this->chunkLength)
		{
			$line = $this->socket->readLine($bufferSize);
			if (!preg_match('/^([0-9a-f]+)/i', $line, $matches))
			{
				throw new LogicException (
					"Cannot decode chunked response, invalid chunk length '{$line}'"
				);
			}
			else
			{
				$this->chunkLength = hexdec($matches[1]);
				// Chunk with zero length indicates the end
				if (0 == $this->chunkLength)
				{
					$this->socket->readLine($bufferSize);
					return '';
				}
			}
		}
		$data = $this->socket->read(min($this->chunkLength, $bufferSize));
		$this->chunkLength -= strlen($data);
		if (0 == $this->chunkLength)
		{
			$this->socket->readLine($bufferSize); // Trailing CRLF
		}
		return $data;
	}
}

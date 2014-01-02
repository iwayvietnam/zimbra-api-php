<?php
/**
 * This file is part of the Zimbra API in PHP library.
 *
 * © Nguyen Van Nguyen <nguyennv1981@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Adapter for Request wrapping around pecl_http extension
 * @package   Zimbra
 * @category  Http
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
class ZAP_HTTP_Adapter_Http extends ZAP_HTTP_Adapter
{
	/**
	 * Mapping of method names to HttpRequest methods
	 * @var  array
	 */
	protected static $methodMap = array(
		ZAP_HTTP_Request::METHOD_OPTIONS => HTTP_METH_OPTIONS,
		ZAP_HTTP_Request::METHOD_GET     => HTTP_METH_GET,
		ZAP_HTTP_Request::METHOD_HEAD    => HTTP_METH_HEAD,
		ZAP_HTTP_Request::METHOD_POST    => HTTP_METH_POST,
		ZAP_HTTP_Request::METHOD_PUT     => HTTP_METH_PUT,
		ZAP_HTTP_Request::METHOD_DELETE  => HTTP_METH_DELETE,
		ZAP_HTTP_Request::METHOD_TRACE   => HTTP_METH_TRACE,
		ZAP_HTTP_Request::METHOD_CONNECT => HTTP_METH_CONNECT,
	);

	/**
	 * Mapping of SSL context options to cURL options
	 * @var  array
	 */
	protected static $sslContextMap = array(
		'ssl_verify_peer' => 'verifypeer',
		'ssl_cafile'      => 'cainfo',
		'ssl_capath'      => 'capath',
		'ssl_local_cert'  => 'cert',
		'ssl_passphrase'  => 'keypasswd'
	);

	/**
	 * HttpRequest class
	 * @var HttpRequest
	 */
	protected $httpRequest;

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
		if (!extension_loaded('http'))
		{
			throw new LogicException('http extension not available');
		}
		$this->request = $request;
		$this->response = NULL;

		try
		{
			$this->httpRequest = $this->createHttpRequest();
			$response = $this->send();
		}
		catch (Exception $e){}
		unset($this->request, $this->requestBody);

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
	 * Sends the request body
	 *
	 * @return Response
	 */
	protected function send()
	{
		if (in_array($this->request->method(), self::$bodyDisallowed))
		{
			return;
		}
		$position   = 0;
		$bufferSize = $this->request->config('buffer_size');
		$body = '';
		while ($position < $this->contentLength)
		{
			if (is_string($this->requestBody))
			{
				$body .= substr($this->requestBody, $position, $bufferSize);
			}
			elseif (is_resource($this->requestBody))
			{
				$body .= fread($this->requestBody, $bufferSize);
			}
			else
			{
				$body .= $this->requestBody->read($bufferSize);
			}
			$position += strlen($body);
		}
		$this->httpRequest->setBody($body);
		$httpMessage = $this->httpRequest->send();
		$status = 'HTTP/' . $httpMessage->getHttpVersion() . ' ' . $httpMessage->getResponseCode() . ' ' . $httpMessage->getResponseStatus();
		$response = new ZAP_HTTP_Response(
			$status, FALSE, $this->request->url()
		);
		$headers = $httpMessage->getHeaders();
		foreach ($headers as $key => $value)
		{
			$response->parseHeaderLine($key . ': ' . $value);
		}
		if ($this->request->config('store_body'))
		{
			$response->appendBody($httpMessage->getBody());
		}

		return $response;
	}

	/**
	 * Creates a new HttpRequest instance and populates it with data from the request
	 *
	 * @return HttpRequest
	 * @throws LogicException
	 */
	protected function createHttpRequest()
	{
		$options = array(
			// connection timeout
			'connecttimeout' => $this->request->config('connect_timeout'),
		);
		// request timeout
		if ($timeout = $this->request->config('timeout'))
		{
			$options['timeout'] = $timeout;
		}
		// set HTTP version
		switch ($this->request->config('protocol_version'))
		{
			case '1.0':
				$options['protocol'] = HTTP_VERSION_1_0;
				break;
			case '1.1':
				$options['protocol'] = HTTP_VERSION_1_1;
				break;
			default:
				$options['protocol'] = HTTP_VERSION_ANY;
		}
		// set up redirects
		if ($this->request->config('follow_redirects'))
		{
			$options['redirect'] = $this->request->config('max_redirects');
		}

		// set proxy, if needed
		if ($host = $this->request->config('proxy_host'))
		{
			if (!($port = $this->request->config('proxy_port')))
			{
				throw new LogicException('Proxy port not provided');
			}				
			$options['proxyhost'] = $host;
			$options['proxyport'] = (int) $port;
			if ($user = $this->request->config('proxy_user'))
			{
				$options['proxyauth'] = $user . ':' . $this->request->config('proxy_password');
				switch ($this->request->config('proxy_auth_scheme'))
				{
					case ZAP_HTTP_Request::AUTH_BASIC:
						$options['proxyauthtype'] = HTTP_AUTH_BASIC;
						break;
					case ZAP_HTTP_Request::AUTH_DIGEST:
						$options['proxyauthtype'] = HTTP_AUTH_DIGEST;
						break;
				}
			}
			if ($type = $this->request->config('proxy_type'))
			{
				switch ($type)
				{
					case 'http':
						$options['proxytype'] = HTTP_PROXY_HTTP;
						break;
					case 'socks5':
						$options['proxytype'] = HTTP_PROXY_SOCKS5;
						break;
					default:
						throw new LogicException ("Proxy type '{$type}' is not supported");
				}
			}
		}

		// set authentication data
		if ($auth = $this->request->auth())
		{
			$options['httpauth'] = $auth['user'] . ':' . $auth['password'];
			switch ($auth['scheme'])
			{
				case ZAP_HTTP_Request::AUTH_BASIC:
					$options['httpauthtype'] = HTTP_AUTH_BASIC;
					break;
				case ZAP_HTTP_Request::AUTH_DIGEST:
					$options['httpauthtype'] = HTTP_AUTH_DIGEST;
					break;
			}
		}
		// set SSL options
		foreach ($this->request->config() as $name => $value)
		{
			if ('ssl_verify_host' == $name && NULL !== $value)
			{
				$options['ssl']['verifyhost'] = $value ?  2 : 0;
			}
			elseif (isset(self::$sslContextMap[$name]) && NULL !== $value)
			{
				$options['ssl'][self::$sslContextMap[$name]] = $value;
			}
		}

		$headers = $this->request->headers();
		if (!isset($headers['accept-encoding']))
		{
			$headers['accept-encoding'] = '';
		}
		if (($jar = $this->request->cookieJar()) AND ($cookies = $jar->matching($this->request->url(), TRUE)))
		{
			$headers['cookie'] = (empty($headers['cookie'])? '': $headers['cookie'] . '; ') . $cookies;
		}
		$headersFmt = array();
		foreach ($headers as $name => $value)
		{
			$canonicalName = implode('-', array_map('ucfirst', explode('-', $name)));
			$headersFmt[$canonicalName] = $value;
		}
		if(count($headersFmt))
		{
			$options['headers'] = $headersFmt;
		}

		$method = $this->request->method();
		$httpRequest = new HttpRequest(
			$this->request->url()->url(),
			isset(self::$methodMap[$method]) ? self::$methodMap[$method] : HTTP_METH_GET,
			$options
		);
		$this->calculateRequestLength($headers);

		return $httpRequest;
	}
}

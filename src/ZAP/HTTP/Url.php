<?php
/**
 * Url, a class representing a URL as per RFC 3986.
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2007-2009, Peytz & Co. A/S
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the distribution.
 *   * Neither the name of the Net_URL2 nor the names of its contributors may
 *     be used to endorse or promote products derived from this software
 *     without specific prior written permission.
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
 * Represents a URL as per RFC 3986.
 * 
 * @package   Zimbra
 * @category  Http
 * @author    Christian Schmidt <schmidt@php.net>
 * @copyright 2007-2009 Peytz & Co. A/S
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class ZAP_HTTP_Url
{
	/**
	 * Do strict parsing in resolve() (see RFC 3986, section 5.2.2). Default
	 * is true.
	 */
	const OPTION_STRICT = 'strict';

	/**
	 * Represent arrays in query using PHP's [] notation. Default is true.
	 */
	const OPTION_USE_BRACKETS = 'use_brackets';

	/**
	 * URL-encode query variable keys. Default is true.
	 */
	const OPTION_ENCODE_KEYS = 'encode_keys';

	/**
	 * Query variable separators when parsing the query string. Every character
	 * is considered a separator. Default is "&".
	 */
	const OPTION_SEPARATOR_INPUT = 'input_separator';

	/**
	 * Query variable separator used when generating the query string. Default
	 * is "&".
	 */
	const OPTION_SEPARATOR_OUTPUT = 'output_separator';

	/**
	 * Default options corresponds to how PHP handles $_GET.
	 */
	private $_options = array(
		self::OPTION_STRICT           => TRUE,
		self::OPTION_USE_BRACKETS     => TRUE,
		self::OPTION_ENCODE_KEYS      => TRUE,
		self::OPTION_SEPARATOR_INPUT  => '&',
		self::OPTION_SEPARATOR_OUTPUT => '&',
	);

	/**
	 * @var  string
	 */
	private $_scheme;

	/**
	 * @var  string
	 */
	private $_userInfo;

	/**
	 * @var  string
	 */
	private $_host;

	/**
	 * @var  string|bool
	 */
	private $_port;

	/**
	 * @var  string
	 */
	private $_path = '';

	/**
	 * @var  string
	 */
	private $_query;

	/**
	 * @var  string
	 */
	private $_fragment;

	/**
	 * Constructor.
	 *
	 * @param string $url     an absolute or relative URL
	 * @param array  $options an array of OPTION_xxx constants
	 *
	 * @return self
	 * @uses   self::parseUrl()
	 */
	public function __construct($url, array $options = array())
	{
		foreach ($options as $name => $value)
		{
			if (array_key_exists($name, $this->_options))
			{
				$this->_options[$name] = $value;
			}
		}

		$this->parseUrl($url);
	}

	/**
	 * Get or set scheme.
	 *
	 * @param string $scheme e.g. "http" or "urn", or false if there is no scheme specified, i.e. if this is a relative URL
	 * @return string|self
	 */
	public function scheme($scheme = NULL)
	{
		if(NULL === $scheme)
		{
			return $this->_scheme;
		}
		$this->_scheme = (string) $scheme;
		return $this;
	}
	
	/**
	 * Returns the user part of the userInfo part (the part preceding the first
	 *  ":"), or false if there is no userInfo part.
	 *
	 * @return  string
	 */
	public function user()
	{
		return $this->_userInfo !== NULL
			? preg_replace('@:.*$@', '', $this->_userInfo)
			: NULL;
	}
	
	/**
	 * Returns the password part of the userInfo part (the part after the first
	 *  ":"), or false if there is no userInfo part (i.e. the URL does not
	 * contain "@" in front of the hostname) or the userInfo part does not
	 * contain ":".
	 *
	 * @return  string
	 */
	public function password()
	{
		return $this->_userInfo !== false
			? substr(strstr($this->_userInfo, ':'), 1)
			: NULL;
	}

	/**
	 * Get or set userInfo part. i.e. if the authority part does not contain "@".
	 *
	 * @param string $userInfo userInfo or username
	 * @param string $password optional password, or NULL
	 * @return string|self
	 */
	public function userInfo($userInfo = NULL, $password = NULL)
	{
		if(NULL === $userInfo)
		{
			return $this->_userInfo;
		}
		$this->_userInfo = $userInfo;
		if ($password !== NULL)
		{
			$this->_userInfo .= ':' . $password;
		}
		return $this;
	}

	/**
	 * Get or set host
	 *
	 * @param string $host a hostname, an IP address, or NULL
	 * @return string|self
	 */
	public function host($host = NULL)
	{
		if(NULL === $host)
		{
			return $this->_host;
		}
		$this->_host = (string) $host;
		return $this;
	}

	/**
	 * Get or set port
	 *
	 * @param string $port a port number
	 * @return string|self
	 */
	public function port($port = NULL)
	{
		if(NULL === $port)
		{
			return $this->_port;
		}
		$this->_port = (string) $port;
		return $this;
	}

	/**
	 * Get or set authority
	 *
	 * @param string $authority a hostname or an IP addresse, possibly with userInfo prefixed and port number appended, e.g. "foo:bar@example.org:81".
	 * @return string|self
	 */
	public function authority($authority = NULL)
	{
		if(NULL === $authority)
		{
			if (!$this->_host)
			{
				return NULL;
			}
			$authority = '';
			if ($this->_userInfo !== NULL)
			{
				$authority .= $this->_userInfo . '@';
			}
			$authority .= $this->_host;
			if ($this->_port !== NULL)
			{
				$authority .= ':' . $this->_port;
			}
			return $authority;
		}
		else
		{
			$this->_userInfo = NULL;
			$this->_host     = NULL;
			$this->_port     = NULL;
			if (preg_match('@^(([^\@]*)\@)?([^:]+)(:(\d*))?$@', $authority, $reg))
			{
				if ($reg[1])
				{
					$this->_userInfo = $reg[2];
				}

				$this->_host = $reg[3];
				if (isset($reg[5]))
				{
					$this->_port = $reg[5];
				}
			}
			return $this;
		}
	}

	/**
	 * Get or set path
	 *
	 * @param string $path a path
	 * @return string|self
	 */
	public function path($path = NULL)
	{
		if(NULL === $path)
		{
			return $this->_path;
		}
		$this->_path = (string) $path;
		return $this;
	}

	/**
	 * Get or set query
	 *
	 * @param string $query a query string, e.g. "foo=1&bar=2"
	 * @return string|self
	 */
	public function query($query = NULL)
	{
		if(NULL === $query)
		{
			return $this->_query;
		}
		$this->_query = (string) $query;
		return $this;
	}

	/**
	 * Get or set fragment
	 *
	 * @param string $fragment a fragment excluding the leading "#", or NULL
	 * @return string|self
	 */
	public function fragment($fragment = NULL)
	{
		if(NULL === $fragment)
		{
			return $this->_fragment;
		}
		$this->_fragment = (string) $fragment;
		return $this;
	}

	/**
	 * Get or set ets the query string to the specified variable in the query string.
	 *
	 * @param array $variables (name => value) array
	 * @return string|self
	 */
	public function queryVariables(array $variables = NULL)
	{
		if(NULL === $variables)
		{
			$pattern = '/[' .
					   preg_quote($this->option(self::OPTION_SEPARATOR_INPUT), '/') .
					   ']/';
			$parts   = preg_split($pattern, $this->_query, -1, PREG_SPLIT_NO_EMPTY);
			$return  = array();

			foreach ($parts as $part)
			{
				if (strpos($part, '=') !== FALSE)
				{
					list($key, $value) = explode('=', $part, 2);
				}
				else
				{
					$key   = $part;
					$value = NULL;
				}

				if ($this->option(self::OPTION_ENCODE_KEYS))
				{
					$key = rawurldecode($key);
				}
				$value = rawurldecode($value);

				if ($this->option(self::OPTION_USE_BRACKETS) AND preg_match('#^(.*)\[([0-9a-z_-]*)\]#i', $key, $matches))
				{
					$key = $matches[1];
					$idx = $matches[2];

					// Ensure is an array
					if (empty($return[$key]) OR !is_array($return[$key]))
					{
						$return[$key] = array();
					}

					// Add data
					if ($idx === '')
					{
						$return[$key][] = $value;
					}
					else
					{
						$return[$key][$idx] = $value;
					}
				}
				elseif (!$this->option(self::OPTION_USE_BRACKETS) AND !empty($return[$key]))
				{
					$return[$key]   = (array) $return[$key];
					$return[$key][] = $value;
				}
				else
				{
					$return[$key] = $value;
				}
			}

			return $return;
		}
		else
		{
			if (count($variables) == 0)
			{
				$this->_query = NULL;
			}
			else
			{
				$this->_query = $this->buildQuery(
					$array,
					$this->option(self::OPTION_SEPARATOR_OUTPUT)
				);
			}
		}
	}
	
	/**
	 * Sets the specified variable in the query string.
	 *
	 * @param string $name  variable name
	 * @param mixed  $value variable value
	 * @return string|self
	 */
	public function queryVariable($name, $value = NULL)
	{
		$variables = $this->queryVariables();
		if(NULL === $value)
		{
			return isset($variables[$name]) ? $variables[$name] : NULL;
		}
		else
		{
			$variables[$name] = $value;
			$this->queryVariables($variables);
			return $this;
		}
	}

	/**
	 * Removes the specifed variable from the query string.
	 *
	 * @param string $name a query string variable, e.g. "foo" in "?foo=1"
	 * @return void
	 */
	public function removeQueryVariable($name)
	{
		$array = $this->queryVariables();
		unset($array[$name]);
		$this->queryVariables($array);
	}

	/**
	 * Returns a string representation of this URL.
	 *
	 * @return  string
	 */
	public function url()
	{
		$url = '';

		if ($this->_scheme !== NULL)
		{
			$url .= $this->_scheme . ':';
		}

		$authority = $this->authority();
		if ($authority !== NULL)
		{
			$url .= '//' . $authority;
		}
		$url .= $this->_path;

		if ($this->_query !== NULL)
		{
			$url .= '?' . $this->_query;
		}

		if ($this->_fragment !== NULL)
		{
			$url .= '#' . $this->_fragment;
		}
	
		return $url;
	}

	/**
	 * Returns a string representation of this URL.
	 *
	 * @return  string
	 * @see toString()
	 */
	public function __toString()
	{
		return $this->url();
	}

	/** 
	 * Returns a normalized string representation of this URL. This is useful for comparison of URLs.
	 *
	 * @return  string
	 */
	public function normalizedUrl()
	{
		$url = clone $this;
		return $url->normalize()->url();
	}

	/** 
	 * Returns a normalized Url instance.
	 *
	 * @return Url
	 */
	public function normalize()
	{
		// Schemes are case-insensitive
		if ($this->_scheme)
		{
			$this->_scheme = strtolower($this->_scheme);
		}

		// Hostnames are case-insensitive
		if ($this->_host)
		{
			$this->_host = strtolower($this->_host);
		}

		// Remove default port number for known schemes (RFC 3986, section 6.2.3)
		if ($this->_port AND $this->_scheme AND $this->_port == getservbyname($this->_scheme, 'tcp'))
		{
			$this->_port = NULL;
		}

		// Normalize case of %XX percentage-encodings (RFC 3986, section 6.2.2.1)
		foreach (array('_userInfo', '_host', '_path') as $part)
		{
			if ($this->$part)
			{
				$this->$part = preg_replace('/%[0-9a-f]{2}/ie', 'strtoupper("\0")', $this->$part);
			}
		}

		$this->_path = self::removeDotSegments($this->_path);

		if ($this->_host && !$this->_path)
		{
			$this->_path = '/';
		}
		return $this;
	}

	/**
	 * Returns whether this instance represents an absolute URL.
	 *
	 * @return  bool
	 */
	public function isAbsolute()
	{
		return (bool) $this->_scheme;
	}

	/**
	 * Returns an Url instance representing an absolute URL relative to this URL.
	 *
	 * @param Url|string $reference relative URL
	 * @return Url
	 */
	public function resolve($reference)
	{
		if (!$reference instanceof ZAP_HTTP_Url)
		{
			$reference = new self($reference);
		}
		if (!$this->isAbsolute())
		{
			throw new InvalidArgumentException('Base-URL must be absolute');
		}

		// A non-strict parser may ignore a scheme in the reference if it is
		// identical to the base URI's scheme.
		if (!$this->option(self::OPTION_STRICT) AND $reference->_scheme == $this->_scheme)
		{
			$reference->_scheme = NULL;
		}

		$target = new self('');
		if ($reference->_scheme !== NULL)
		{
			$target->_scheme = $reference->_scheme;
			$target->authority($reference->authority());
			$target->_path  = self::removeDotSegments($reference->_path);
			$target->_query = $reference->_query;
		}
		else
		{
			$authority = $reference->authority();
			if ($authority !== NULL)
			{
				$target->authority($authority);
				$target->_path  = self::removeDotSegments($reference->_path);
				$target->_query = $reference->_query;
			}
			else
			{
				if ($reference->_path == '')
				{
					$target->_path = $this->_path;
					if ($reference->_query !== NULL)
					{
						$target->_query = $reference->_query;
					}
					else
					{
						$target->_query = $this->_query;
					}
				}
				else
				{
					if (substr($reference->_path, 0, 1) == '/')
					{
						$target->_path = self::removeDotSegments($reference->_path);
					}
					else
					{
						if ($this->_host !== NULL AND $this->_path == '')
						{
							$target->_path = '/' . $this->_path;
						}
						else
						{
							$i = strrpos($this->_path, '/');
							if ($i !== FALSE)
							{
								$target->_path = substr($this->_path, 0, $i + 1);
							}
							$target->_path .= $reference->_path;
						}
						$target->_path = self::removeDotSegments($target->_path);
					}
					$target->_query = $reference->_query;
				}
				$target->authority($this->authority());
			}
			$target->_scheme = $this->_scheme;
		}

		$target->_fragment = $reference->_fragment;

		return $target;
	}

	/**
	 * Get or set the value of the specified option.
	 *
	 * @param string $name The name of the option
	 * @param string $value The option value
	 * @return  mixed|self
	 */
	public function option($name, $value = NULL)
	{
		if(NULL === $value)
		{
			return isset($this->_options[$name]) ? $this->_options[$name] : NULL;
		}
		else
		{
			if (array_key_exists($name, $this->_options))
			{
				$this->_options[$name] = $value;
			}
			return $this;
		}
	}

	/**
	 * A simple version of http_build_query in userland. The encoded string is
	 * percentage encoded according to RFC 3986.
	 *
	 * @param array  $data      An array, which has to be converted into
	 *                          QUERY_STRING. Anything is possible.
	 * @param string $seperator See {@link self::OPTION_SEPARATOR_OUTPUT}
	 * @param string $key       For stacked values (arrays in an array).
	 *
	 * @return string
	 */
	protected function buildQuery(array $data, $separator, $key = NULL)
	{
		$query = array();
		foreach ($data as $name => $value)
		{
			if ($this->getOption(self::OPTION_ENCODE_KEYS) === TRUE)
			{
				$name = rawurlencode($name);
			}
			if ($key !== NULL)
			{
				if ($this->getOption(self::OPTION_USE_BRACKETS) === TRUE)
				{
					$name = $key . '[' . $name . ']';
				}
				else
				{
					$name = $key;
				}
			}
			if (is_array($value))
			{
				$query[] = $this->buildQuery($value, $separator, $name);
			}
			else
			{
				$query[] = $name . '=' . rawurlencode($value);
			}
		}
		return implode($separator, $query);
	}

	/**
	 * This method uses a funky regex to parse the url into the designated parts.
	 *
	 * @param string $url
	 * @return void
	 */
	protected function parseUrl($url)
	{
		preg_match('!^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?!',
				   $url,
				   $matches);

		$this->_scheme   = !empty($matches[1]) ? $matches[2] : NULL;
		$this->authority(!empty($matches[3]) ? $matches[4] : NULL);
		$this->_path     = $matches[5];
		$this->_query    = !empty($matches[6]) ? $matches[7] : NULL;
		$this->_fragment = !empty($matches[8]) ? $matches[9] : NULL;
	}

	/**
	 * Removes dots as described in RFC 3986, section 5.2.4, e.g.
	 * "/foo/../bar/baz" => "/bar/baz"
	 *
	 * @param string $path a path
	 * @return string a path
	 */
	public static function removeDotSegments($path)
	{
		$output = '';

		$j = 0;
		while ($path AND $j++ < 100)
		{
			if (substr($path, 0, 2) == './')
			{
				$path = substr($path, 2);
			}
			elseif (substr($path, 0, 3) == '../')
			{
				$path = substr($path, 3);
			}
			elseif (substr($path, 0, 3) == '/./' OR $path == '/.')
			{
				$path = '/' . substr($path, 3);
			}
			elseif (substr($path, 0, 4) == '/../' OR $path == '/..')
			{
				$path   = '/' . substr($path, 4);
				$i      = strrpos($output, '/');
				$output = $i === FALSE ? '' : substr($output, 0, $i);
			}
			elseif ($path == '.' OR $path == '..')
			{
				$path = '';
			}
			else
			{
				$i = strpos($path, '/');
				if ($i === 0)
				{
					$i = strpos($path, '/', 1);
				}
				if ($i === FALSE)
				{
					$i = strlen($path);
				}
				$output .= substr($path, 0, $i);
				$path = substr($path, $i);
			}
		}

		return $output;
	}

	/**
	 * Percent-encodes all non-alphanumeric characters except these: _ . - ~
	 * Similar to PHP's rawurlencode(), except that it also encodes ~ in PHP
	 * 5.2.x and earlier.
	 *
	 * @param  $raw the string to encode
	 * @return string
	 */
	public static function urlencode($string)
	{
		$encoded = rawurlencode($string);

		// This is only necessary in PHP < 5.3.
		$encoded = str_replace('%7E', '~', $encoded);
		return $encoded;
	}

	/**
	 * Returns a Url instance representing the canonical URL of the currently executing PHP script.
	 *
	 * @return  string
	 */
	public static function canonical()
	{
		if (!isset($_SERVER['REQUEST_METHOD']))
		{
			throw new RuntimeException('Script was not called through a webserver');
		}

		// Begin with a relative URL
		$url = new self($_SERVER['PHP_SELF']);
		$url->_scheme = isset($_SERVER['HTTPS']) ? 'https' : 'http';
		$url->_host   = $_SERVER['SERVER_NAME'];
		$port = $_SERVER['SERVER_PORT'];
		if ($url->_scheme == 'http' AND $port != 80 OR $url->_scheme == 'https' AND $port != 443)
		{
			$url->_port = $port;
		}
		return $url;
	}

	/**
	 * Returns the URL used to retrieve the current request.
	 *
	 * @return  string
	 */
	public static function requestedUrl()
	{
		return self::requested()->url();
	}

	/**
	 * Returns a Url instance representing the URL used to retrieve the current request.
	 *
	 * @return  Url
	 */
	public static function requested()
	{
		if (!isset($_SERVER['REQUEST_METHOD']))
		{
			throw new RuntimeException('Script was not called through a webserver');
		}

		// Begin with a relative URL
		$url = new self($_SERVER['REQUEST_URI']);
		$url->_scheme = isset($_SERVER['HTTPS']) ? 'https' : 'http';
		// Set host and possibly port
		$url->authority($_SERVER['HTTP_HOST']);
		return $url;
	}
}

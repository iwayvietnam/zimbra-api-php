<?php
/**
 * Helper class for building multipart/form-data request body
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
 * Class for building multipart/form-data request body
 *
 * The class helps to reduce memory consumption by streaming large file uploads
 * from disk, it also allows monitoring of upload progress
 * 
 * @package  Zimbra
 * @category Http
 * @author   Alexey Borzov <avb@php.net>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 */
class ZAP_HTTP_Multipart
{
	/**
	 * MIME boundary
	 * @var  string
	 */
	private $_boundary;

	/**
	 * Form post parameters
	 * @var  array
	 */
	private $_postParams = array();

	/**
	 * File uploads
	 * @var  array
	 */
	private $_files = array();

	/**
	 * Header for parts with parameters
	 * @var  string
	 */
	private $_paramPart = "--%s\r\nContent-Disposition: form-data; name=\"%s\"\r\n\r\n";

	/**
	 * Header for parts with uploads
	 * @var  string
	 */
	private $_uploadPart = "--%s\r\nContent-Disposition: form-data; name=\"%s\"; filename=\"%s\"\r\nContent-Type: %s\r\n\r\n";

	/**
	 * Current position in parameter and upload arrays
	 *
	 * First number is index of "current" part, second number is position within "current" part
	 *
	 * @var  array
	 */
	private $_pos = array(0, 0);

	/**
	 * Constructor. Sets the arrays with POST data.
	 *
	 * @param array $params      values of form fields set via
	 * @param array $files       file uploads
	 * @param bool  $useBrackets whether to append brackets to array variable names
	 */
	public function __construct(array $params, array $files, $useBrackets = true)
	{
		$this->_postParams = self::_flattenArray('', $params, $useBrackets);
		foreach ($files as $name => $f)
		{
			if (!is_array($f['fp']))
			{
				$this->_files[] = $f + array('name' => $name);
			}
			else
			{
				for ($i = 0; $i < count($f['fp']); $i++)
				{
					$upload = array(
						'name' => ($useBrackets? $name . '[' . $i . ']': $name)
					);
					foreach (array('fp', 'filename', 'size', 'type') as $key)
					{
						$upload[$key] = $f[$key][$i];
					}
					$this->_files[] = $upload;
				}
			}
		}
	}

	/**
	 * Returns the length of the body to use in Content-Length header
	 *
	 * @return   integer
	 */
	public function length()
	{
		$boundaryLength     = strlen($this->boundary());
		$headerParamLength  = strlen($this->_paramPart) - 4 + $boundaryLength;
		$headerUploadLength = strlen($this->_uploadPart) - 8 + $boundaryLength;
		$length             = $boundaryLength + 6;
		foreach ($this->_postParams as $p)
		{
			$length += $headerParamLength + strlen($p[0]) + strlen($p[1]) + 2;
		}
		foreach ($this->_files as $u)
		{
			$length += $headerUploadLength + strlen($u['name']) + strlen($u['type']) + strlen($u['filename']) + $u['size'] + 2;
		}
		return $length;
	}

	/**
	 * Returns the boundary to use in Content-Type header
	 *
	 * @return   string
	 */
	public function boundary()
	{
		if (empty($this->_boundary))
		{
			$this->_boundary = '--' . md5('ZAP_HTTP_Request-' . microtime());
		}
		return $this->_boundary;
	}

	/**
	 * Returns next chunk of request body
	 *
	 * @param integer $length Number of bytes to read
	 *
	 * @return   string  Up to $length bytes of data, empty string if at end
	 */
	public function read($length)
	{
		$ret         = '';
		$boundary    = $this->boundary();
		$paramCount  = count($this->_postParams);
		$uploadCount = count($this->_files);
		while ($length > 0 AND $this->_pos[0] <= $paramCount + $uploadCount)
		{
			$oldLength = $length;
			if ($this->_pos[0] < $paramCount)
			{
				$param = sprintf(
					$this->_paramPart, $boundary, $this->_postParams[$this->_pos[0]][0]
				) . $this->_postParams[$this->_pos[0]][1] . "\r\n";
				$ret    .= substr($param, $this->_pos[1], $length);
				$length -= min(strlen($param) - $this->_pos[1], $length);

			}
			elseif ($this->_pos[0] < $paramCount + $uploadCount)
			{
				$pos    = $this->_pos[0] - $paramCount;
				$header = sprintf(
					$this->_uploadPart, $boundary, $this->_files[$pos]['name'],
					$this->_files[$pos]['filename'], $this->_files[$pos]['type']
				);
				if ($this->_pos[1] < strlen($header))
				{
					$ret    .= substr($header, $this->_pos[1], $length);
					$length -= min(strlen($header) - $this->_pos[1], $length);
				}
				$filePos  = max(0, $this->_pos[1] - strlen($header));
				if ($length > 0 AND $filePos < $this->_files[$pos]['size'])
				{
					$ret     .= fread($this->_files[$pos]['fp'], $length);
					$length  -= min($length, $this->_files[$pos]['size'] - $filePos);
				}
				if ($length > 0)
				{
					$start   = $this->_pos[1] + ($oldLength - $length) -
							   strlen($header) - $this->_files[$pos]['size'];
					$ret    .= substr("\r\n", $start, $length);
					$length -= min(2 - $start, $length);
				}

			}
			else
			{
				$closing  = '--' . $boundary . "--\r\n";
				$ret     .= substr($closing, $this->_pos[1], $length);
				$length  -= min(strlen($closing) - $this->_pos[1], $length);
			}
			if ($length > 0)
			{
				$this->_pos     = array($this->_pos[0] + 1, 0);
			}
			else
			{
				$this->_pos[1] += $oldLength;
			}
		}
		return $ret;
	}

	/**
	 * Sets the current position to the start of the body
	 *
	 * This allows reusing the same body in another request
	 */
	public function rewind()
	{
		$this->_pos = array(0, 0);
		foreach ($this->_files as $u)
		{
			rewind($u['fp']);
		}
	}

	/**
	 * Returns the body as string
	 *
	 * Note that it reads all file uploads into memory so it is a good idea not
	 * to use this method with large file uploads and rely on read() instead.
	 *
	 * @return   string
	 */
	public function __toString()
	{
		$this->rewind();
		return $this->read($this->length());
	}


	/**
	 * Helper function to change the (probably multidimensional) associative array
	 * into the simple one.
	 *
	 * @param string $name        name for item
	 * @param mixed  $values      item's values
	 * @param bool   $useBrackets whether to append [] to array variables' names
	 *
	 * @return   array   array with the following items: array('item name', 'item value');
	 */
	private static function _flattenArray($name, $values, $useBrackets)
	{
		if (!is_array($values))
		{
			return array(array($name, $values));
		}
		else
		{
			$ret = array();
			foreach ($values as $k => $v)
			{
				if (empty($name))
				{
					$newName = $k;
				}
				elseif ($useBrackets)
				{
					$newName = $name . '[' . $k . ']';
				}
				else
				{
					$newName = $name;
				}
				$ret = array_merge($ret, self::_flattenArray($newName, $v, $useBrackets));
			}
			return $ret;
		}
	}
}

<?php
/**
 * SOCKS5 proxy connection class
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
 * * SOCKS5 proxy connection class (used by Socket Adapter)
 * 
 * @package   Zimbra
 * @category  Http
 * @author    Alexey Borzov <avb@php.net>
 * @license   http://opensource.org/licenses/bsd-license.php New BSD License
 */
class ZAP_HTTP_Wrapper_SOCKS5 extends ZAP_HTTP_Wrapper_SocketWrapper
{
	/**
	 * Constructor, tries to connect and authenticate to a SOCKS5 proxy
	 *
	 * @param string $address    Proxy address, e.g. 'tcp://localhost:1080'
	 * @param int    $timeout    Connection timeout (seconds)
	 * @param array  $sslOptions SSL context options
	 * @param string $username   Proxy user name
	 * @param string $password   Proxy password
	 *
	 * @throws RuntimeException
	 */
	public function __construct(
		$address, $timeout = 10, array $sslOptions = array(),
		$username = NULL, $password = NULL
	)
	{
		parent::__construct($address, $timeout, $sslOptions);

		if (strlen($username))
		{
			$request = pack('C4', 5, 2, 0, 2);
		}
		else
		{
			$request = pack('C3', 5, 1, 0);
		}
		$this->write($request);
		$response = unpack('Cversion/Cmethod', $this->read(3));
		if (5 != $response['version'])
		{
			throw new RuntimeException(
				'Invalid version received from SOCKS5 proxy: ' . $response['version']
			);
		}
		switch ($response['method'])
		{
			case 2:
				$this->performAuthentication($username, $password);
			case 0:
				break;
			default:
				throw new RuntimeException(
					"Connection rejected by proxy due to unsupported auth method"
				);
		}
	}

	/**
	 * Performs username/password authentication for SOCKS5
	 *
	 * @param string $username Proxy user name
	 * @param string $password Proxy password
	 *
	 * @throws RuntimeException
	 */
	protected function performAuthentication($username, $password)
	{
		$request = pack('C2', 1, strlen($username)) . $username
				 . pack('C', strlen($password)) . $password;

		$this->write($request);
		$response = unpack('Cvn/Cstatus', $this->read(3));
		if (1 != $response['vn'] || 0 != $response['status'])
		{
			throw new RuntimeException(
				'Connection rejected by proxy due to invalid username and/or password'
			);
		}
	}

	/**
	 * Connects to a remote host via proxy
	 *
	 * @param string $remoteHost Remote host
	 * @param int    $remotePort Remote port
	 *
	 * @throws RuntimeException
	 */
	public function connect($remoteHost, $remotePort)
	{
		$request = pack('C5', 0x05, 0x01, 0x00, 0x03, strlen($remoteHost))
				 . $remoteHost . pack('n', $remotePort);

		$this->write($request);
		$response = unpack('Cversion/Creply/Creserved', $this->read(1024));
		if (5 != $response['version'] || 0 != $response['reserved'])
		{
			throw new RuntimeException(
				'Invalid response received from SOCKS5 proxy'
			);
		}
		elseif (0 != $response['reply'])
		{
			throw new RuntimeException(
				"Unable to connect to {$remoteHost}:{$remotePort} through SOCKS5 proxy"
			);
		}
	}
}

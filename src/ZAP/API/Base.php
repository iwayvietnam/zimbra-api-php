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
 * ZAP_API_Base is a base class which allows to manage Zimbra
 * @package   ZAP
 * @category  API
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
abstract class ZAP_API_Base
{
	/**
	 * @var string The Zimbra api soap location
	 */
	protected $_location = 'https://localhost/service/soap';

	/**
	 * @var string The user account
	 */
	protected $_account;
	/**
	 * @var ZAP_Client_Interface Zimbra api soap client
	 */
	protected $_client;

	/**
	 * @var string The soap namespace
	 */
	protected $_namespace = 'urn:zimbra';

	/**
	 * Get Zimbra api soap client.
	 *
	 * @return mix
	 */
	public function client()
	{
		return $this->_client;
	}

	/**
	 * Get Zimbra api soap location.
	 *
	 * @return mix
	 */
	public function location()
	{
		return $this->_location;
	}

	/**
	 * Process attributes params.
	 *
	 * @param  array $attrs Array of attribute.
	 * @return mix
	 */
	protected function _attributes(array $attrs, $name = 'n', array $inArray = array())
	{
		$params = array();
		if(count($attrs))
		{
			foreach ($attrs as $key => $value)
			{
				$params[] = array(
					$name => $key,
					'_' => $value,
				);
			}
		}
		return $params;
	}

	protected function _commaAttributes(array $attrs = array(), array $inArray = array())
	{
		$commaStr = '';
		foreach ($attrs as $attr)
		{
			if(count($inArray) AND !in_array($attr, $inArray))
			{
				continue;
			}
			if(empty($commaStr))
				$commaStr = $attr;
			else
				$commaStr .= ','.$attr;
		}
		return $commaStr;
	}

	protected function _processCondFilter(array $cond)
	{
		$result = array();
		$condKeys = array('not', 'attr', 'op', 'value');
		foreach ($cond as $key => $value)
		{
			if(in_array($key, $condKeys))
			{
				$filter[$key] = $value;
			}
		}
		return $result;
	}

	protected function _processCondsFilter(array $conds)
	{
		$result = array();
		if(isset($conds['not']))
		{
			$not = (int)$conds['not'];
			$result['not'] = ($not > 0) ? 1 : 0;
			unset($conds['not']);
		}
		if(isset($conds['or']))
		{
			$or = (int)$conds['or'];
			$result['or'] = ($or > 0) ? 1 : 0;
			unset($conds['or']);
		}
		if(isset($conds['cond']) AND is_array($conds['cond']))
		{
			$result['cond'] = $this->_processCondFilter($conds['cond']);
			unset($conds['cond']);
		}
		if(isset($conds['conds']) AND is_array($conds['conds']))
		{
			$result['conds'] = $this->_processCondsFilter($conds['conds']);
			unset($conds['conds']);
		}
		return $result;
	}
}

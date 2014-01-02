<?php
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
 * ZAP_Soap_Struct_XParam class
 * @package   ZAP
 * @category  Soap
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
class ZAP_Soap_Struct_XParam
{
	/**
	 * xParam Name
	 * - use : required
	 * @var string
	 */
	private $_name;

	/**
	 * xParam value
	 * - use : required
	 * @var string
	 */
	private $_value;

	/**
	 * Constructor method for xParam
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function __construct($name, $value)
	{
		$this->name($name)->value($value);
	}

	/**
	 * Get or set xParam name
	 *
	 * @param  string $name xParam name
	 * @return string|ZAP_Soap_Struct_XParam
	 */
	public function name($name = NULL)
	{
		if(NULL === $name)
		{
			return $this->_name;
		}
		$this->_name = $name;
		return $this;
	}

	/**
	 * Get or set xParam value
	 *
	 * @param  string $name xParam value
	 * @return string|ZAP_Soap_Struct_XParam
	 */
	public function value($value = NULL)
	{
		if(NULL === $value)
		{
			return $this->_value;
		}
		$this->_value = $value;
		return $this;
	}

	/**
	 * Returns the array representation of this class 
	 *
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'name' => $this->_name,
			'value' => $this->_value,				
		);
	}

	/**
	 * Method returning the xml representation of this class
	 *
	 * @return SimpleXMLElement
	 */
	public function toXml()
	{
		$xml = simplexml_load_string('<xparam></xparam>');
		$xml->addAttribute('name', $this->_name)
			->addAttribute('value', $this->_value);
		return $xml;
	}

	/**
	 * Method returning the xml string representation of this class
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toXml()->asXml();
	}
}

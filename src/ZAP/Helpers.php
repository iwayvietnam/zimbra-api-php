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
 * Helper class. Provides simple methods for working with data.
 *
 * @package   ZAP
 * @author	Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
class ZAP_Helpers
{
	/**
	 * Convert array to SimpleXMLElement object.
	 *
	 * @param  string $name	  The name of root element.
	 * @param  array  $array	 Array.
	 * @param  string $namespace Namespace.
	 * @return SimpleXMLElement
	 */
	public static function arrayToXml($name, array $array, $namespace = NULL)
	{
		if(!empty($namespace))
		{
			$xmlString = strtr('<ns:{name} xmlns:ns="{namespace}"></ns:{name}>', array('{name}' => $name, '{namespace}' => $namespace));
		}
		else
		{
			$xmlString = strtr('<{name}></{name}>', array('{name}' => $name));
		}
		$xml = simplexml_load_string($xmlString);
		self::_addArrayToXml($xml, $array, $namespace);
		return $xml;
	}

	/**
	 * Convert SimpleXMLElement object to stdClass object.
	 *
	 * @param  SimpleXMLElement $xml The xml object.
	 * @return object
	 */
	public static function xmlToObject(SimpleXMLElement $xml)
	{
		$attributes = $xml->attributes();
		$children = $xml->children();
		$textValue = trim((string)$xml);
		if(count($attributes) === 0 AND count($children) === 0)
		{
			return $textValue;
		}
		else
		{
			$object = new StdClass();
			foreach($attributes as $key => $value)
			{
				$object->$key = (string)$value;
			}
			if(!empty($textValue))
			{
				$object->_ = $textValue;
			}
			foreach($children as $value)
			{
				$name = $value->getName();
				if(isset($object->$name))
				{
					if(is_array($object->$name))
					{
						array_push($object->$name, self::xmlToObject($value));
					}
					else
					{
						$object->$name = array($object->$name, self::xmlToObject($value));
					}
				}
				else
				{
					$object->$name = self::xmlToObject($value);
				}
			}
			return $object;
		}
	}

	public static function appendXML(SimpleXMLElement $xml, SimpleXMLElement $append)
	{
		if($append)
		{
			if(strlen(trim((string) $append)) === 0)
			{
				$newChild = $xml->addChild($append->getName());
				foreach($append->children() as $child)
				{
					self::appendXML($newChild, $child);
				} 
			}
			else
			{
				$newChild = $xml->addChild($append->getName(), (string) $append);
			}
			foreach($append->attributes() as $name => $value)
			{
				$newChild->addAttribute($name, $value);
			}
		} 
	}

	/**
	 * Check the tag is valid.
	 *
	 * @param  string $tag The tag name.
	 * @return bool
	 */
	public static function isValidTagName($tag)
	{
		$pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
		return preg_match($pattern, $tag, $matches) AND $matches[0] == $tag;
	}

	/**
	 * Convert bool value to string.
	 *
	 * @param  string $tag The tag name.
	 * @return string
	 */
	public static function boolToString($value)
	{
		$value = $value === true ? 'true' : $value;
		$value = $value === false ? 'false' : $value;
		return $value;
	}

	/**
	 * Extract header string to array.
	 *
	 * @param  string $headerString Header string.
	 * @return array
	 */
	public static function extractHeaders($headerString = '')
	{
		$parts = explode("\r\n", $headerString);
		$headers = array();
		foreach ($parts as $part)
		{
			$pos = strpos($part, ':');
			if($pos)
			{
				$name = trim(substr($part, 0, $pos));
				$value = trim(substr($part, ($pos + 1)));
				$headers[$name] = $value;
			}
		}
		return $headers;		
	}

	/**
	 * Add an array to xml.
	 *
	 * @param  SimpleXMLElement $xml.
	 * @param  array			$array.
	 * @param  string		   $namespace.
	 * @return void
	 */
	private static function _addArrayToXml(SimpleXMLElement $xml, array $array = array(), $namespace = NULL)
	{
		foreach ($array as $name => $param)
		{
			if (is_array($param) AND ZAP_Helpers::isValidTagName($name))
			{
				$textValue = NULL;
				if(isset($param['_']))
				{
					$textValue = $param['_'];
					unset($param['_']);
				}

				if(is_numeric(key($param)))
				{
					foreach($param as $value)
					{
						if(is_array($value))
						{
							self::_addArrayToXml($xml, array($name => $value));
						}
						else
						{
							$xml->addChild($name, ZAP_Helpers::boolToString($value), $namespace);
						}
					}
				}
				else
				{
					$child = $xml->addChild($name, ZAP_Helpers::boolToString($textValue), $namespace);
					foreach($param as $key => $value)
					{
						if(!self::isValidTagName($key))
						{
							throw new Exception('Illegal character in tag name. tag: '.$key);
						}
						if(is_array($value))
						{
							if(is_numeric(key($value)))
							{
								foreach($value as $k => $v)
								{
									if(is_array($v))
									{
										self::_addArrayToXml($child, array($key => $v));
									}
									else
									{
										$child->addChild($key, ZAP_Helpers::boolToString($v), $namespace);
									}
								}
							}
							else
							{
								self::_addArrayToXml($child, array($key => $value));
							}
						}
						else
						{
							$child->addAttribute($key, ZAP_Helpers::boolToString($value));
						}
					}
				}
			}
			else
			{
				if(!self::isValidTagName($name))
				{
					throw new Exception('Illegal character in tag name. tag: '.$name);
				}
				$xml->addChild($name, ZAP_Helpers::boolToString($param), $namespace);
			}
		}
	}
}

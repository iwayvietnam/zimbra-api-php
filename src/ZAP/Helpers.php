<?php
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
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
class ZAP_Helpers
{
    /**
     * Convert array to SimpleXMLElement object.
     *
     * @param $name The name of root element.
     * @param $array Array.
     * @param $namespace Namespace.
     * @return mix
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
        $xml = new SimpleXMLElement($xmlString);
        self::_addArrayToXml($xml, $array, $namespace);
        return $xml;
    }

    /**
     * Convert SimpleXMLElement object to stdClass object.
     *
     * @param $xml The xml object.
     * @return mix
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

    /**
     * Check the tag is valid.
     *
     * @param $tag The tag name.
     * @return mix
     */
    public static function isValidTagName($tag)
    {
        $pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
        return preg_match($pattern, $tag, $matches) AND $matches[0] == $tag;
    }

    /**
     * Convert bool value to string.
     *
     * @param $tag The tag name.
     * @return mix
     */
    public static function boolToString($value)
    {
        $value = $value === true ? 'true' : $value;
        $value = $value === false ? 'false' : $value;
        return $value;
    }

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

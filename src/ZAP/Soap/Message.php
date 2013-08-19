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
 * ZAP_Soap_Message class
 * @package   ZAP
 * @category  Soap
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
class ZAP_Soap_Message
{
	/**
	 * @var SimpleXMLElement
	 */
	private $_xml;

	/**
	 * @var SimpleXMLElement
	 */
	private $_header;

	/**
	 * @var string The xml namespace
	 */
	private $_namespace;

	/**
	 * ZAP_Soap_Message constructor
	 *
	 * @param string $namespace The xml namespace.
	 */
	public function __construct($namespace = 'urn:zimbra')
	{
		$this->_namespace = empty($namespace) ? 'urn:zimbra' : $namespace;
		if($this->_namespace === 'urn:zimbra')
		{
			$message = 
				'<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" '
							 .'xmlns:urn="urn:zimbra">'
				.'</env:Envelope>';
		}
		else
		{
			$message = 
				'<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" '
							 .'xmlns:urn="urn:zimbra" '
							 .'xmlns:urn1="'.$this->_namespace.'">'
				.'</env:Envelope>';
		}
		$this->_xml = new SimpleXMLElement($message);
		$this->_header = $this->_xml->addChild('Header')
							  ->addChild('context', NULL, 'urn:zimbra');
		$this->_xml->addChild('Body');
	}

	/**
	 * Add header.
	 *
	 * @param  string $name  Header name.
	 * @param  string $value Header value
	 * @return ZAP_Soap_Message
	 */
	public function addHeader($name, $value)
	{
		if(isset($this->_header->$name))
		{
			$this->_header->$name = $value;
		}
		else
		{
			$this->_header->addChild($name, $value);
		}
		return $this;
	}

	/**
	 * Set soap body.
	 *
	 * @param  string $name   Soap function name.
	 * @param  array  $attrs  Soap function attributes
	 * @param  array  $params Soap function params
	 * @return ZAP_Soap_Message
	 */
	public function setBody($name, $attrs = array(), $params = array())
	{
		unset($this->_xml->children('env', TRUE)->Body);
		$body = $child = $this->_xml->addChild('Body');
		if(isset($params['_']))
		{
			$body->addChild($name, (string) $params['_'], $this->_namespace);
		}
		else
		{
			$child = $body->addChild($name, NULL, $this->_namespace);
			$this->_processParams($child, $params);
		}

		foreach ($attrs as $key => $value)
		{
			if(ZAP_Helpers::isValidTagName($key))
			{
				$child->addAttribute($key, $value);				
			}
		}
		return $this;
	}

	/**
	 * Process soap response body.
	 *
	 * @param  string $response Soap response message.
	 * @throws ZAP_Exception
	 * @return mix
	 */
    public function processResponse($response)
    {
    	$xml = simplexml_load_string($response);
    	$fault = $xml->children('soap', TRUE)->Body->Fault;
    	if ($fault)
    	{
    		throw new ZAP_Exception($fault->children('soap', TRUE)->Reason->Text);
    	}
    	return ZAP_Helpers::xmlToObject($xml->children('soap', TRUE)->Body);
    }

	/**
	 * Get namespace.
	 *
	 * @return string Namespace string
	 */
	public function getNamespace()
	{
		return $this->_namespace;
	}

	/**
	 * Return a well-formed XML string.
	 *
	 * @return string Xml string
	 */
    public function __toString()
    {
        return trim($this->_xml->asXml());
    }

	/**
	 * Process soap parameters.
	 *
	 * @param  SimpleXMLElement $xml    SimpleXMLElement object.
	 * @param  array            $params Parametters.
	 * @return void
	 */
    private function _processParams(SimpleXMLElement $xml, array $params = array())
    {
    	foreach ($params as $name => $param)
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
	    					$this->_processParams($xml, array($name => $value));
	    				}
	    				else
	    				{
	    					$xml->addChild($name, ZAP_Helpers::boolToString($value), $this->_namespace);
	    				}
	    			}
	    		}
	    		else
	    		{
		            $child = $xml->addChild($name, ZAP_Helpers::boolToString($textValue), $this->_namespace);
					foreach($param as $key => $value)
	    			{
			            if(ZAP_Helpers::isValidTagName($key))
			            {
				            if(is_array($value))
				            {
				            	if(is_numeric(key($value)))
				            	{
					                foreach($value as $k => $v)
					                {
					                	if(is_array($v))
					                	{
					                		$this->_processParams($child, array($key => $v));
					                	}
					                	else
					                	{
					                		$child->addChild($key, ZAP_Helpers::boolToString($v), $this->_namespace);
					                	}
					                }
				            	}
				            	else
				            	{
				            		$this->_processParams($child, array($key => $value));
				            	}
				            }
				            else
				            {
				            	$child->addAttribute($key, ZAP_Helpers::boolToString($value));
				            }
			            }
	    			}
	    		}
    		}
    		else
    		{
    			if(ZAP_Helpers::isValidTagName($name))
    			{
    				$xml->addChild($name, ZAP_Helpers::boolToString($param), $this->_namespace);
    			}
    		}
    	}
    }
}
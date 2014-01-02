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
 * ZAP_Soap_Struct_CalendarAttendee class
 * @package   ZAP
 * @category  Soap
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
class ZAP_Soap_Struct_CalendarAttendee
{
	/**
	 * Non-standard parameters (XPARAMs)
	 * @var array Array of ZAP_Soap_Struct_XParam
	 */
	private $_xparams;

	/**
	 * Email address (without "MAILTO:")
	 * @var string
	 */
	private $_a;

	/**
	 * URL - has same value as {email-address}. 
	 * @var string
	 */
	private $_url;

	/**
	 * Friendly name - "CN" in iCalendar
	 * @var string
	 */
	private $_d;

	/**
	 * iCalendar SENT-BY
	 * @var string
	 */
	private $_sentBy;

	/**
	 * iCalendar DIR - Reference to a directory entry associated with the calendar user. the property.
	 * @var string
	 */
	private $_dir;

	/**
	 * iCalendar LANGUAGE - As defined in RFC5646 * (e.g. "en-US")
	 * @var string
	 */
	private $_lang;

	/**
	 * iCalendar CUTYPE (Calendar user type)
	 * @var string
	 */
	private $_cutype;

	/**
	 * iCalendar ROLE
	 * @var string
	 */
	private $_role;

	/**
	 * iCalendar PTST (Participation status).
	 * Valid values: NE|AC|TE|DE|DG|CO|IN|WE|DF
	 * Meanings: "NE"eds-action, "TE"ntative, "AC"cept, "DE"clined, "DG" (delegated), "CO"mpleted (todo), "IN"-process (todo), "WA"iting (custom value only for todo), "DF" (deferred; custom value only for todo)
	 * @var string
	 */
	private $_ptst;

	/**
	 * iCalendar RSVP
	 * @var boolean
	 */
	private $_rsvp;

	/**
	 * iCalendar MEMBER - The group or list membership of the calendar user
	 * @var string
	 */
	private $_member;

	/**
	 * iCalendar DELEGATED-TO
	 * @var string
	 */
	private $_delTo;

	/**
	 * iCalendar DELEGATED-FROM
	 * @var string
	 */
	private $_delFrom;

	/**
	 * Add xparam
	 *
	 * @param  ZAP_Soap_Struct_XParam $xparam
	 * @return ZAP_Soap_Struct_CalendarAttendee
	 */
	public function addXParam(ZAP_Soap_Struct_XParam $xparam)
	{
		$this->_xparams[] = $xparam;
		return $this;
	}

	/**
	 * Get array of xparam
	 *
	 * @return array Array of ZimbraServiceStructXParam
	 */
	public function xParams()
	{
		return $this->_xparams;
	}

	/**
	 * Get or set email address
	 *
	 * @param  string $a email address
	 * @return string|ZAP_Soap_Struct_CalendarAttendee
	 */
	public function a($a = NULL)
	{
		if(NULL === $a)
		{
			return $this->_a;
		}
		$this->_a = (string) $a;
		return $this;
	}

	/**
	 * Get or set url value
	 *
	 * @param  string $a url url value
	 * @return string|ZAP_Soap_Struct_CalendarAttendee
	 */
	public function url($url = NULL)
	{
		if(NULL === $url)
		{
			return $this->_url;
		}
		$this->_url = (string) $url;
		return $this;
	}

	/**
	 * Get or set friendly name
	 *
	 * @param  string $d friendly name
	 * @return string|ZAP_Soap_Struct_CalendarAttendee
	 */
	public function d($d = NULL)
	{
		if(NULL === $d)
		{
			return $this->_d;
		}
		$this->_d = (string) $d;
		return $this;
	}

	/**
	 * Get or set iCalendar SENT-BY
	 *
	 * @param  string $sentBy iCalendar SENT-BY
	 * @return string|ZAP_Soap_Struct_CalendarAttendee
	 */
	public function sentBy($sentBy = NULL)
	{
		if(NULL === $sentBy)
		{
			return $this->_sentBy;
		}
		$this->_sentBy = (string) $sentBy;
		return $this;
	}

	/**
	 * Get or set iCalendar DIR
	 *
	 * @param  string $dir iCalendar DIR
	 * @return string|ZAP_Soap_Struct_CalendarAttendee
	 */
	public function dir($dir = NULL)
	{
		if(NULL === $dir)
		{
			return $this->_dir;
		}
		$this->_dir = (string) $dir;
		return $this;
	}

	/**
	 * Get or set iCalendar LANGUAGE
	 *
	 * @param  string $lang iCalendar LANGUAGE
	 * @return string|ZAP_Soap_Struct_CalendarAttendee
	 */
	public function lang($lang = NULL)
	{
		if(NULL === $lang)
		{
			return $this->_lang;
		}
		$this->_lang = (string) $lang;
		return $this;
	}

	/**
	 * Get or set iCalendar CUTYPE
	 *
	 * @param  string $cutype iCalendar CUTYPE
	 * @return string|ZAP_Soap_Struct_CalendarAttendee
	 */
	public function cutype($cutype = NULL)
	{
		if(NULL === $cutype)
		{
			return $this->_cutype;
		}
		$this->_cutype = (string) $cutype;
		return $this;
	}

	/**
	 * Get or set iCalendar ROLE
	 *
	 * @param  string $cutype iCalendar ROLE
	 * @return string|ZAP_Soap_Struct_CalendarAttendee
	 */
	public function role($role = NULL)
	{
		if(NULL === $role)
		{
			return $this->_role;
		}
		$this->_role = (string) $role;
		return $this;
	}

	/**
	 * Get or set iCalendar PTST
	 * Valid values: NE|AC|TE|DE|DG|CO|IN|WE|DF
	 *
	 * @param  string $ptst iCalendar PTST
	 * @return string|ZAP_Soap_Struct_CalendarAttendee
	 */
	public function ptst($ptst = NULL)
	{
		if(NULL === $ptst)
		{
			return $this->_ptst;
		}
		$validValues = array('NE', 'AC', 'TE', 'DE', 'DG', 'CO', 'IN', 'WE', 'DF');
		if(in_array($ptst, $validValues))
		{
			$this->_ptst = (string) $ptst;
			return $this;
		}
		else
		{
			throw new RuntimeException("Invalid value");
		}
	}

	/**
	 * Get or set iCalendar RSVP
	 *
	 * @param  boolean $rsvp iCalendar RSVP
	 * @return boolean|ZAP_Soap_Struct_CalendarAttendee
	 */
	public function rsvp($rsvp = NULL)
	{
		if(NULL === $rsvp)
		{
			return $this->_rsvp;
		}
		$this->_rsvp = (bool) $rsvp;
		return $this;
	}

	/**
	 * Get or set iCalendar MEMBER
	 *
	 * @param  string $member iCalendar MEMBER
	 * @return string|ZAP_Soap_Struct_CalendarAttendee
	 */
	public function member($member = NULL)
	{
		if(NULL === $member)
		{
			return $this->_member;
		}
		$this->_member = (string) $member;
		return $this;
	}

	/**
	 * Get or set iCalendar DELEGATED-TO
	 *
	 * @param  string $delTo iCalendar DELEGATED-TO
	 * @return string|ZAP_Soap_Struct_CalendarAttendee
	 */
	public function delTo($delTo = NULL)
	{
		if(NULL === $delTo)
		{
			return $this->_delTo;
		}
		$this->_delTo = (string) $delTo;
		return $this;
	}

	/**
	 * Get or set iCalendar DELEGATED-FROM
	 *
	 * @param  string $delTo iCalendar DELEGATED-FROM
	 * @return string|ZAP_Soap_Struct_CalendarAttendee
	 */
	public function delFrom($delFrom = NULL)
	{
		if(NULL === $delFrom)
		{
			return $this->_delFrom;
		}
		$this->_delFrom = (string) $delFrom;
		return $this;
	}

	public function toArray()
	{
		$arr = array();
		if(!empty($this->_a)) $arr['a'] = $this->_a;
		if(!empty($this->_url)) $arr['url'] = $this->_url;
		if(!empty($this->_d)) $arr['d'] = $this->_d;
		if(!empty($this->_sentBy)) $arr['sentBy'] = $this->_sentBy;
		if(!empty($this->_dir)) $arr['dir'] = $this->_dir;
		if(!empty($this->_lang)) $arr['lang'] = $this->_lang;
		if(!empty($this->_cutype)) $arr['cutype'] = $this->_cutype;
		if(!empty($this->_role)) $arr['role'] = $this->_role;
		if(!empty($this->_ptst)) $arr['ptst'] = $this->_ptst;
		if($this->_rsvp) $arr['rsvp'] = (bool) $this->_rsvp ? 1 : 0;
		if(!empty($this->_member)) $arr['member'] = $this->_member;
		if(!empty($this->_delTo)) $arr['delTo'] = $this->_delTo;
		if(!empty($this->_delFrom)) $arr['delFrom'] = $this->_delFrom;
		if(count($this->_xparams))
		{
			$arr['xparam'] = array();
			foreach ($this->_xparams as $xparam)
			{
				$arr['xparam'][] = $this->_xparam->toArray();
			}
		}
		return $arr;
	}

	/**
	 * Method returning the xml representative this class
	 *
	 * @return SimpleXMLElement
	 */
	public function toXml()
	{
		$xml = simplexml_load_string('<at></at>');
		if(!empty($this->_a)) $xml->addAttribute('a', $this->_a);
		if(!empty($this->_url)) $xml->addAttribute('url', $this->_url);
		if(!empty($this->_d)) $xml->addAttribute('d', $this->_d);
		if(!empty($this->_sentBy)) $xml->addAttribute('sentBy', $this->_sentBy);
		if(!empty($this->_dir)) $xml->addAttribute('dir', $this->_dir);
		if(!empty($this->_lang)) $xml->addAttribute('lang', $this->_lang);
		if(!empty($this->_cutype)) $xml->addAttribute('cutype', $this->_cutype);
		if(!empty($this->_role)) $xml->addAttribute('role', $this->_role);
		if(!empty($this->_ptst)) $xml->addAttribute('ptst', $this->_ptst);
		if($this->_rsvp) $xml->addAttribute('rsvp', (bool) $this->_rsvp ? 1 : 0);
		if(!empty($this->_member)) $xml->addAttribute('member', $this->_member);
		if(!empty($this->_delTo)) $xml->addAttribute('delTo', $this->_delTo);
		if(!empty($this->_delFrom)) $xml->addAttribute('delFrom', $this->_delFrom);
		if(count($this->_xparams))
		{
			foreach ($this->_xparams as $xparam)
			{
				$xparamXml = $this->_xparam->toXml();
				ZAP_Helpers::appendXML($xml, $xparamXml);
			}
		}
		return $xml;
	}

	/**
	 * Method returning the xml string representative this class
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toXml()->asXml();
	}
}

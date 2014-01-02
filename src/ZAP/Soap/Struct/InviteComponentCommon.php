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
 * ZAP_Soap_Struct_InviteComponentCommon class
 * @package   ZAP
 * @category  Soap
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
class ZAP_Soap_Struct_InviteComponentCommon
{
	/**
	 * The method
	 * - use : required
	 * @var string
	 */
	private $_method;

	/**
	 * Component number of the invite
	 * - use : required
	 * @var int
	 */
	private $_compNum;

	/**
	 * RSVP flag. Set if response requested, unset if no response requested
	 * - use : required
	 * @var boolean
	 */
	private $_rsvp;

	/**
	 * Priority (0 - 9; default = 0)
	 * @var int
	 */
	private $_priority = 0;

	/**
	 * The name
	 * @var string
	 */
	private $_name;

	/**
	 * Location
	 * @var string
	 */
	private $_loc;

	/**
	 * Percent complete for VTODO (0 - 100; default = 0)
	 * @var int
	 */
	private $_percentComplete = 0;

	/**
	 * VTODO COMPLETED DATE-TIME in format yyyyMMddThhmmssZ
	 * @var string
	 */
	private $_completed;

	/**
	 * Set if invite has no blob data, i.e. all data is in db metadata
	 * @var boolean
	 */
	private $_noBlob;

	/**
	 * The "actual" free-busy status of this invite (ie what the client should display).
	 * This is synthesized taking into account our Attendee's PartStat, the Opacity of the appointment, its Status, etc... 
	 * Valid values - F|B|T|U. i.e. Free, Busy (default), busy-Tentative, OutOfOffice (busy-unavailable)
	 * @var string
	 */
	private $_fba;

	/**
	 * FreeBusy setting F|B|T|U 
	 * i.e. Free, Busy (default), busy-Tentative, OutOfOffice (busy-unavailable)
	 * @var string
	 */
	private $_fb;

	/**
	 * Transparency - O|T. i.e. Opaque or Transparent
	 * @var string
	 */
	private $_transp;

	/**
	 * Am I the organizer? [default 0 (false)]
	 * @var boolean
	 */
	private $_isOrg;

	/**
	 * The x_uid
	 * @var string
	 */
	private $_x_uid;

	/**
	 * UID to use when creating appointment. Optional: client can request the UID to use
	 * @var string
	 */
	private $_uid;

	/**
	 * Sequence number (default = 0)
	 * @var int
	 */
	private $_seq;

	/**
	 * Date - used for zdsync
	 * @var long
	 */
	private $_d;

	/**
	 * Mail item ID of appointment
	 * @var string
	 */
	private $_calItemId;

	/**
	 * Appointment ID (deprecated)
	 * @var string
	 */
	private $_apptId;

	/**
	 * Folder of appointment
	 * @var string
	 */
	private $_ciFolder;

	/**
	 * Status - TENT|CONF|CANC|NEED|COMP|INPR|WAITING|DEFERRED
	 * i.e. TENTative, CONFirmed, CANCelled, COMPleted, INPRogress, WAITING, DEFERRED where waiting
	 * and Deferred are custom values not found in the iCalendar spec.
	 * @var string
	 */
	private $_status;

	/**
	 * Class = PUB|PRI|CON. i.e. PUBlic (default), PRIvate, CONfidential
	 * @var string
	 */
	private $_class;

	/**
	 * The url
	 * @var string
	 */
	private $_url;

	/**
	 * Set if this is invite is an exception
	 * @var boolean
	 */
	private $_ex;

	/**
	 * Recurrence-id string in UTC timezone
	 * @var string
	 */
	private $_ridZ;

	/**
	 * Set if is an all day appointment
	 * @var boolean
	 */
	private $_allDay;

	/**
	 * Set if invite has changes that haven't been sent to attendees; for organizer only
	 * @var boolean
	 */
	private $_draft;

	/**
	 * Set if attendees were never notified of this invite; for organizer only
	 * @var boolean
	 */
	private $_neverSent;

	/**
	 * Comma-separated list of changed data in an updated invite.
	 * Possible values are "subject", "location", "time" (start time, end time, or duration), and "recurrence".
	 * @var string
	 */
	private $_changes;

	/**
	 * Constructor method for xParam
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function __construct($method, $compNum, $rsvp)
	{
		$this->method($method)
			 ->compNum($compNum)
			 ->rsvp($rsvp);
	}

	/**
	 * Get or set method
	 *
	 * @param  string $method
	 * @return string|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function method($method = NULL)
	{
		if(NULL === $method)
		{
			return $this->_method;
		}
		$this->_method = (string) $method;
		return $this;
	}

	/**
	 * Get or set compNum
	 *
	 * @param  int $compNum
	 * @return int|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function compNum($compNum = NULL)
	{
		if(NULL === $compNum)
		{
			return $this->_compNum;
		}
		$this->_compNum = (int) $compNum;
		return $this;
	}

	/**
	 * Get or set rsvp
	 *
	 * @param  boolean $rsvp
	 * @return boolean|ZAP_Soap_Struct_InviteComponentCommon
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
	 * Get or set priority
	 *
	 * @param  int $priority
	 * @return int|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function priority($priority = NULL)
	{
		if(NULL === $priority)
		{
			return $this->_priority;
		}
		if((int) $priority > 0 AND (int) $priority < 10)
		{
			$this->_priority = (int) $priority;
		}
		return $this;
	}

	/**
	 * Get or set name
	 *
	 * @param  string $name
	 * @return string|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function name($name = NULL)
	{
		if(NULL === $name)
		{
			return $this->_name;
		}
		$this->_name = (string) $name;
		return $this;
	}

	/**
	 * Get or set loc
	 *
	 * @param  string $loc
	 * @return string|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function loc($loc = NULL)
	{
		if(NULL === $loc)
		{
			return $this->_loc;
		}
		$this->_loc = (string) $loc;
		return $this;
	}

	/**
	 * Get or set seq
	 *
	 * @param  int $seq
	 * @return int|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function percentComplete($percentComplete = NULL)
	{
		if(NULL === $percentComplete)
		{
			return $this->_percentComplete;
		}
		if((int) $percentComplete > 0 AND (int) $percentComplete <= 100)
		{
			$this->_percentComplete = (int) $percentComplete;
		}
		return $this;
	}

	/**
	 * Get or set completed
	 * VTODO COMPLETED DATE-TIME in format yyyyMMddThhmmssZ
	 *
	 * @param  string $completed
	 * @return string|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function completed($completed = NULL)
	{
		if(NULL === $completed)
		{
			return $this->_completed;
		}
		$this->_completed = (string) $completed;
		return $this;
	}

	/**
	 * Get or set ex
	 *
	 * @param  boolean $noBlob
	 * @return boolean|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function noBlob($noBlob = NULL)
	{
		if(NULL === $noBlob)
		{
			return $this->_noBlob;
		}
		$this->_noBlob = (bool) $noBlob;
		return $this;
	}

	/**
	 * Get or set "actual" free-busy status
	 * Valid values - F|B|T|U. i.e. Free, Busy (default), busy-Tentative, OutOfOffice (busy-unavailable)
	 *
	 * @param  string $fb
	 * @return string|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function fba($fba = NULL)
	{
		if(NULL === $fba)
		{
			return $this->_fba;
		}
		$validValues = array('F', 'B', 'T', 'U');
		if(in_array($fba, $validValues))
		{
			$this->_fba = (string) $fba;
			return $this;
		}
		else
		{
			throw new RuntimeException("Invalid value");
		}
	}

	/**
	 * Get or set FreeBusy setting
	 * Valid values: F|B|T|U 
	 * i.e. Free, Busy (default), busy-Tentative, OutOfOffice (busy-unavailable)
	 *
	 * @param  string $fb
	 * @return string|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function fb($fb = NULL)
	{
		if(NULL === $fb)
		{
			return $this->_fb;
		}
		$validValues = array('F', 'B', 'T', 'U');
		if(in_array($fb, $validValues))
		{
			$this->_fb = (string) $fb;
			return $this;
		}
		else
		{
			throw new RuntimeException("Invalid value");
		}
	}

	/**
	 * Get or set transparency
	 * Valid values: O|T. i.e. Opaque or Transparent
	 *
	 * @param  string $transp
	 * @return string|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function transp($transp = NULL)
	{
		if(NULL === $transp)
		{
			return $this->_transp;
		}
		$validValues = array('O', 'T');
		if(in_array($transp, $validValues))
		{
			$this->_transp = (string) $transp;
			return $this;
		}
		else
		{
			throw new RuntimeException("Invalid value");
		}
	}

	/**
	 * Get or set isOrg
	 *
	 * @param  boolean $isOrg
	 * @return boolean|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function isOrg($isOrg = NULL)
	{
		if(NULL === $isOrg)
		{
			return $this->_isOrg;
		}
		$this->_isOrg = (bool) $isOrg;
		return $this;
	}

	/**
	 * Get or set x_uid
	 *
	 * @param  string $x_uid
	 * @return string|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function x_uid($x_uid = NULL)
	{
		if(NULL === $x_uid)
		{
			return $this->_x_uid;
		}
		$this->_x_uid = (string) $x_uid;
		return $this;
	}

	/**
	 * Get or set uid
	 *
	 * @param  string $uid
	 * @return string|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function uid($uid = NULL)
	{
		if(NULL === $uid)
		{
			return $this->_uid;
		}
		$this->_uid = (string) $uid;
		return $this;
	}

	/**
	 * Get or set seq
	 *
	 * @param  int $seq
	 * @return int|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function seq($seq = NULL)
	{
		if(NULL === $seq)
		{
			return $this->_seq;
		}
		$this->_seq = (int) $seq;
		return $this;
	}

	/**
	 * Get or set d
	 *
	 * @param  int $d
	 * @return int|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function d($d = NULL)
	{
		if(NULL === $d)
		{
			return $this->_d;
		}
		$this->_d = (int) $d;
		return $this;
	}

	/**
	 * Get or set calItemId
	 *
	 * @param  string $calItemId
	 * @return string|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function calItemId($calItemId = NULL)
	{
		if(NULL === $calItemId)
		{
			return $this->_calItemId;
		}
		$this->_calItemId = (string) $calItemId;
		return $this;
	}

	/**
	 * Get or set apptId
	 *
	 * @param  string $apptId
	 * @return string|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function apptId($apptId = NULL)
	{
		if(NULL === $apptId)
		{
			return $this->_apptId;
		}
		$this->_apptId = (string) $apptId;
		return $this;
	}

	/**
	 * Get or set ciFolder
	 *
	 * @param  string $ciFolder
	 * @return string|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function ciFolder($ciFolder = NULL)
	{
		if(NULL === $ciFolder)
		{
			return $this->_ciFolder;
		}
		$this->_ciFolder = (string) $ciFolder;
		return $this;
	}

	/**
	 * Get or set status
	 * Valid values: TENT|CONF|CANC|NEED|COMP|INPR|WAITING|DEFERRED
	 * i.e. TENTative, CONFirmed, CANCelled, COMPleted, INPRogress, WAITING, DEFERRED where waiting and Deferred are custom values not found in the iCalendar spec.
	 *
	 * @param  string $status
	 * @return string|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function status($status = NULL)
	{
		if(NULL === $status)
		{
			return $this->_status;
		}
		$validValues = array('TENT', 'CONF', 'CANC', 'NEED', 'COMP', 'INPR', 'WAITING', 'DEFERRED'); 
		if(in_array($status, $validValues))
		{
			$this->_status = (string) $status;
			return $this;
		}
		else
		{
			throw new RuntimeException("Invalid value");
		}
	}

	/**
	 * Get or set class
	 * Valid values: PUB|PRI|CON
	 *
	 * @param  string $class
	 * @return string|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function class($class = NULL)
	{
		if(NULL === $class)
		{
			return $this->_class;
		}
		$validValues = array('PUB', 'PRI', 'CON');
		if(in_array($class, $validValues))
		{
			$this->_class = (string) $class;
			return $this;
		}
		else
		{
			throw new RuntimeException("Invalid value");
		}
	}

	/**
	 * Get or set url
	 *
	 * @param  string $url
	 * @return string|ZAP_Soap_Struct_InviteComponentCommon
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
	 * Get or set ex
	 *
	 * @param  boolean $ex
	 * @return boolean|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function ex($ex = NULL)
	{
		if(NULL === $ex)
		{
			return $this->_ex;
		}
		$this->_ex = (bool) $ex;
		return $this;
	}

	/**
	 * Get or set ridZ
	 *
	 * @param  string $ridZ
	 * @return string|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function ridZ($ridZ = NULL)
	{
		if(NULL === $ridZ)
		{
			return $this->_ridZ;
		}
		$this->_draft = (string) $ridZ;
		return $this;
	}

	/**
	 * Get or set allDay
	 *
	 * @param  boolean $allDay
	 * @return boolean|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function allDay($allDay = NULL)
	{
		if(NULL === $draft)
		{
			return $this->_draft;
		}
		$this->_draft = (bool) $draft;
		return $this;
	}

	/**
	 * Get or set draft
	 *
	 * @param  boolean $draft
	 * @return boolean|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function draft($draft = NULL)
	{
		if(NULL === $draft)
		{
			return $this->_draft;
		}
		$this->_draft = (bool) $draft;
		return $this;
	}

	/**
	 * Get or set neverSent
	 *
	 * @param  boolean $neverSent
	 * @return boolean|ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function neverSent($neverSent = NULL)
	{
		if(NULL === $neverSent)
		{
			return $this->_neverSent;
		}
		$this->_neverSent = (bool) $neverSent;
		return $this;
	}

	/**
	 * Get array of changes
	 *
	 * @return array Array of changes
	 */
	public function changes()
	{
		return $this->_changes;
	}

	/**
	 * Add change
	 * Valid values: subject|location|time|recurrence
	 *
	 * @param  string $change
	 * @return ZAP_Soap_Struct_InviteComponentCommon
	 */
	public function addChange($change = '')
	{
		$validValues = array('subject', 'location', 'time', 'recurrence');
		if(!in_array((string) $change, $this->_changes) AND in_array((string) $change, $validValues))
		{
			$this->_changes[] = (string) $change;
		}
		return $this;
	}

	public function toArray()
	{
		$arr = array(
			'method' => $this->_method,
			'compNum' => $this->_compNum,
			'rsvp' => $this->_rsvp ? 1 : 0,
		);
		return $arr;
	}
}
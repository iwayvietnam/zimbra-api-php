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
 * ZAP_API_Mail_Base is a abstract class which allows to connect Zimbra API account functions via SOAP
 * @package   ZAP
 * @category  Account
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
abstract class ZAP_API_Mail_Base extends ZAP_API_Mail
{
	/**
	 * ZAP_API_Mail_Base constructor
	 *
	 * @param string $location The Zimbra api soap location.
	 */
	public function __construct($location)
	{
		$this->_location = $location;
		$this->_namespace = 'urn:zimbraAccount';
	}

	/**
	 * Add an invite to an appointment.
	 * The invite corresponds to a VEVENT component.
	 * Based on the UID specified (required),
	 * a new appointment is created in the default calendar if necessary.
	 * If an appointment with the same UID exists,
	 * the appointment is updated with the new invite only if the invite is not outdated,
	 * according to the iCalendar sequencing rule (based on SEQUENCE, RECURRENCE-ID and DTSTAMP).
	 *
	 * @param  array  $message Specification of the message to add.
	 * @param  string $ptst    iCalendar PTST (Participation status). Valid values: NE|AC|TE|DE|DG|CO|IN|WE|DF. Meanings: "NE"eds-action, "TE"ntative, "AC"cept, "DE"clined, "DG" (delegated), "CO"mpleted (todo), "IN"-process (todo), "WA"iting (custom value only for todo), "DF" (deferred; custom value only for todo)
	 * @return mix
	 */
	public function addAppointmentInvite(array $message = array(), $ptst = '')
	{
		$ptstValids = array('NE', 'AC', 'TE', 'DE', 'DG', 'CO', 'IN', 'WE', 'DF');
		$options = array();
		if(!empty($ptst) AND in_array($ptst, $ptstValids)) $options['ptst'] = $ptst;

		return $this->_client->addAppointmentInvite($message, $options);
	}

	/**
	 * Add a comment to the specified item. Currently comments can only be added to documents.
	 *
	 * @param  string $parentId Item ID of parent.
	 * @param  string $text     Comment text.
	 * @return mix
	 */
	public function addComment($parentId, $text)
	{
		$params = array(
			'comment' => array(
				'parentId' => $parentId,
				'text' => $text,
			),
		);
		return $this->_client->addComment($params);
	}


	/**
	 * Add a message.
	 *
	 * @param  array  $message Specification of the message to add.
	 * @param  string $content The entire message's content. (Omit if you specify an "aid" attribute.).
	 * @param  bool   $sent    If set, then do outgoing message filtering if the msg is being added to the Sent folder and has been flagged as sent. Default is unset.
	 * @return mix
	 */
	public function addMsg(array $message, $content = '', $sent = FALSE)
	{
		$params['m'] = array();
		$validKeys = array('f', 't', 'tn', 'l', 'noICal', 'd', 'aid');
		foreach ($message as $key => $value)
		{
			if(in_array($key, $validKeys))
			{
				$params['m'][$key] = $value;
			}
		}
		if(!empty($content))
		{
			$params['m']['content'] = array(
				'_' => $content,
			);
		}
		return $this->_client->addMsg($params, array('filterSent' => $sent ? 1 : 0));
	}

	/**
	 * Add a task invite.
	 *
	 * @param  array  $message Specification of the message to add.
	 * @param  string $ptst    iCalendar PTST (Participation status). Valid values: NE|AC|TE|DE|DG|CO|IN|WE|DF. Meanings: "NE"eds-action, "TE"ntative, "AC"cept, "DE"clined, "DG" (delegated), "CO"mpleted (todo), "IN"-process (todo), "WA"iting (custom value only for todo), "DF" (deferred; custom value only for todo)
	 * @return mix
	 */
	public function addTaskInvite(array $message = array(), $ptst = '')
	{
		$ptstValids = array('NE', 'AC', 'TE', 'DE', 'DG', 'CO', 'IN', 'WE', 'DF');
		$options = array();
		if(!empty($ptst) AND in_array($ptst, $ptstValids)) $options['ptst'] = $ptst;

		return $this->_client->addTaskInvite($message, $options);
	}

	/**
	 * Announce change of organizer.
	 *
	 * @param  string $id ID.
	 * @return mix
	 */
	public function announceOrganizerChange($id)
	{
		return $this->_client->announceOrganizerChange(array(), array('id' => $id));
	}

	/**
	 * Applies one or more filter rules to messages specified by a comma-separated ID list, or returned by a search query.
	 * One or the other can be specified, but not both.
	 * Returns the list of ids of existing messages that were affected.
	 * Note that redirect actions are ignored when applying filter rules to existing messages.
	 *
	 * @param  array $rules  Filter rules.
	 * @param  array $ids    Array of message IDs.
	 * @param  string $query Query string.
	 * @return mix
	 */
	public function applyFilterRules(array $rules, array $ids = array(), $query = '')
	{
		$params['filterRules'] = array();
		if(count($rules))
		{
			$params['filterRules']['filterRule'] = array();
			foreach ($rules as $rule)
			{
				$params['filterRules']['filterRule'][] = array('name' => $rule);
			}
		}
		if(count($ids))
		{
			$params['m'] = array(
				'ids' => implode(',', $ids),
			);
		}
		if(!empty($query)) $params['query'] = $query;
		return $this->_client->applyFilterRules($params);
	}

	/**
	 * Applies one or more filter rules to messages specified by a comma-separated ID list, or returned by a search query.
	 * One or the other can be specified, but not both.
	 * Returns the list of ids of existing messages that were affected.
	 *
	 * @param  array $rules  Filter rules.
	 * @param  array $ids    Array of message IDs.
	 * @param  string $query Query string.
	 * @return mix
	 */
	public function applyOutgoingFilterRules(array $rules, array $ids = array(), $query = '')
	{
		$params['filterRules'] = array();
		if(count($rules))
		{
			$params['filterRules']['filterRule'] = array();
			foreach ($rules as $rule)
			{
				$params['filterRules']['filterRule'][] = array('name' => $rule);
			}
		}
		if(count($ids))
		{
			$params['m'] = array(
				'ids' => implode(',', $ids),
			);
		}
		if(!empty($query)) $params['query'] = $query;
		return $this->_client->applyOutgoingFilterRules($params);
	}

	/**
	 * AutoComplete.
	 *
	 * @param  string $name    Name.
	 * @param  string $type    GAL Search type - default value is "account". Valid values: all|account|resource|group
	 * @param  array  $folders Array of folder IDs.
	 * @param  bool   $incGal  Flag whether to include Global Address Book (GAL).
	 * @param  bool   $needExp Set if the "exp" flag is needed in the response for group entries. Default is unset..
	 * @return mix
	 */
	public function autoComplete($name, $type = 'account', array $folders = array(), $incGal = TRUE, $needExp = FALSE)
	{
		$options = array(
			'name' => $name,
		);
		$validTypes = array('all', 'account', 'resource', 'group');
		if(in_array($type, $validTypes))
		{
			$options['t'] = $type;
		}
		if(count($folders))
		{
			$options['folders'] = implode(',', $folders);			
		}
		$options['needExp'] = (bool) $needExp ? 1 : 0;
		$options['includeGal'] = (bool) $incGal ? 1 : 0;
		return $this->_client->autoComplete($params);
	}


	/**
	 * Resend a message.
	 * Supports (f)rom, (t)o, (c)c, (b)cc, (s)ender "type" on <e> elements 
	 * (these get mapped to Resent-From, Resent-To, Resent-CC, Resent-Bcc, Resent-Sender headers,
	 * which are prepended to copy of existing message) 
	 * Aside from these prepended headers, message is reinjected verbatim
	 *
	 * @param  string $id     ID of message to resend.
	 * @param  array  $emails Email addresses.
	 * @return mix
	 */
	public function bounceMsg($id, array $emails = array())
	{
		$params['m'] = array(
			'id' => $id,
		);
		if(count($emails))
		{
			$params['m']['e'] = array();
			foreach ($emails as $email)
			{
				$arr = array(
					'a' => $email['a']
				);
				if(isset($email['t'])) $$arr['t'] = $email['t'];
				if(isset($email['p'])) $$arr['p'] = $email['p'];
			}
		}
		return $this->_client->bounceMsg($params);
	}


	/**
	 * Browse.
	 *
	 * @param  string  $by    Browse by setting - domains|attachments|objects.
	 * @param  array   $regex Regex string. Return only those results which match the specified regular expression.
	 * @param  integer $max   Return only a maximum number of entries as requested. If more than {max-entries} results exist, the server will return the first {max-entries}, sorted by frequency.
	 * @return mix
	 */
	public function browse($by, $regex = '', $max = 0)
	{
		$options = array(
			'browseBy' => $by,
		);
		if(!empty($regex)) $$options['regex'] = $regex;
		if((int) $max > 0) $$options['maxToReturn'] = (int) $max;
		return $this->_client->browse(array(), $options);
	}

	/**
	 * Cancel appointment.
	 * NOTE: If canceling an exception, the original instance (ie the one the exception was "excepting") WILL NOT be restored when you cancel this exception.
	 * If <inst> is set, then this cancels just the specified instance or range of instances, otherwise it cancels the entire appointment. If <inst> is not set, then id MUST refer to the default invite for the appointment.
	 *
	 * @param  array $inst    Instance recurrence ID information.
	 * @param  array $tz      Definition for TZID referenced by DATETIME in <inst>.
	 * @param  array $message Specification of the message.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	public function cancelAppointment(array $inst = array(), array $tz = array(), array $message = array(), array $attrs = array())
	{
		$params = array();
		if(count($inst))
		{
			$params['inst'] = $inst;
		}
		if(count($tz))
		{
			$params['tz'] = $tz;
		}
		if(count($message))
		{
			$params['m'] = $message;
		}
		if(count($message))
		{
			$params['m'] = $message;
		}
		return $this->_client->cancelAppointment($params, $attrs);
	}


	/**
	 * Cancel task.
	 *
	 * @param  array $inst    Instance recurrence ID information.
	 * @param  array $tz      Definition for TZID referenced by DATETIME in <inst>.
	 * @param  array $message Specification of the message.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	public function cancelTask(array $inst = array(), array $tz = array(), array $message = array(), array $attrs = array())
	{
		$params = array();
		if(count($inst))
		{
			$params['inst'] = $inst;
		}
		if(count($tz))
		{
			$params['tz'] = $tz;
		}
		if(count($message))
		{
			$params['m'] = $message;
		}
		if(count($message))
		{
			$params['m'] = $message;
		}
		return $this->_client->cancelTask($params, $attrs);
	}

	/**
	 * Check device status.
	 *
	 * @param  string $id Device ID.
	 * @return mix
	 */
	public function checkDeviceStatus($id)
	{
		return $this->_client->checkDeviceStatus(array('device' => array('id' => $id)));
	}


	/**
	 * Check if the authed user has the specified right(s) on a target.
	 * If the specified target cannot be found:
	 *  1. if by is "id", throw NO_SUCH_ACCOUNT/NO_SUCH_CALENDAR_RESOURCE
	 *  2. if by is "name", return the default permission for the right.
	 *
	 * @param  string $target The name used to identify the target..
	 * @param  string $type   Target type. Valid values: account|calresource|cos|dl|group|domain|server|ucservice|xmppcomponent|zimlet|config|global
	 * @param  array  $rights Rights to check.
	 * @return mix
	 */
	public function checkPermission($target, $type, array $rights = array())
	{
		$params = array();
		if(!empty($target))
		{
			$validTypes = array('account', 'calresource', 'cos', 'dl', 'group', 'domain', 'server', 'ucservice', 'xmppcomponent', 'zimlet', 'config', 'global');
			$params['target'] = array(
				'by' => 'name',
				'type' => in_array($type, $validTypes) ? $validTypes : 'account',
				'_' => $target,
			);
		}
		return $this->_client->checkPermission($params);
	}

	/**
	 * Check conflicts in recurrence against list of users.
	 * Set all attribute to get all instances, even those without conflicts.
	 * By default only instances that have conflicts are returned.
	 *
	 * @param  array $tz     Timezones info.
	 * @param  array $cancel Expanded recurrence cancel
	 * @param  array $comp   Expanded recurrence invite
	 * @param  array $except Expanded recurrence exception
	 * @param  array $usr    Freebusy user specifications
	 * @param  array $attrs  Attributes
	 * @return mix
	 */
	public function checkRecurConflicts(array $tz, array $cancel, array $comp, array $except, array $usr, array $attrs = array())
	{
		$params = array();
		if(count($tz))
		{
			$params['tz'] = $tz;
		}
		if(count($cancel))
		{
			$params['cancel'] = $cancel;
		}
		if(count($comp))
		{
			$params['comp'] = $comp;
		}
		if(count($except))
		{
			$params['except'] = $except;
		}
		if(count($usr))
		{
			$params['usr'] = $usr;
		}
		return $this->_client->checkRecurConflicts($params, $attrs);
	}

	/**
	 * Check spelling.
	 * Suggested words are listed in decreasing order of their match score.
	 * The "available" attribute specifies whether the server-side spell checking interface is available or not.
	 *
	 * @param  string $dictionary The optional name of the aspell dictionary that will be used to check spelling. If not specified, the the dictionary will be either zimbraPrefSpellDictionary or the one for the account's locale, in that order.
	 * @param  array  $ignores     Array of words to ignore just for this request. These words are added to the user's personal dictionary of ignore words stored as zimbraPrefSpellIgnoreWord.
	 * @return mix
	 */
	public function checkSpelling($text = '', $dictionary = '',array $ignores = array())
	{
		$options = array();
		if(!empty($dictionary))
		{
			$options['dictionary'] = $dictionary;
		}
		if(count($ignores))
		{
			$options['ignore'] = implode(',', $ignores);
		}
		return $this->_client->checkSpelling(array('_' => $text), $options);
	}

	/**
	 * Complete a task instance.
	 *
	 * @param  string $id     ID.
	 * @param  array  $except Exception information.
	 * @param  array  $tz     Timezone information.
	 * @return mix
	 */
	public function completeTaskInstance($id, array $except, array $tz = array())
	{
		$options = array('id' => $id);
		$params['exceptId'] = $except;
		if(count($tz)) $params['tz'] = $tz;
		return $this->_client->completeTaskInstance($params, $options);
	}

	/**
	 * Contact Action.
	 *
	 * @param  array  $action Action selector.
	 * @return mix
	 */
	public function contactAction(array $action)
	{
		return $this->_client->contactAction(array('action' => $action));
	}

	/**
	 * Conv Action.
	 *
	 * @param  array  $action Action selector.
	 * @return mix
	 */
	public function convAction(array $action)
	{
		return $this->_client->convAction(array('action' => $action));
	}

	/**
	 * Propose a new time/location. Sent by meeting attendee to organizer.
	 * The syntax is very similar to CreateAppointmentRequest. 
	 *
	 * @param  array $details Details of counter proposal.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	public function counterAppointment(array $details = array(), array $attrs = array())
	{
		$params = array();
		if(count($details))
		{
			$params['m'] = $details;
		}
		return $this->_client->counterAppointment($params, $attrs);
	}

	/**
	 * This is the API to create a new Appointment, optionally sending out meeting Invitations to other people.
	 *
	 * @param  array $message Specification of the message.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	public function createAppointment(array $message = array(), array $attrs = array())
	{
		$params = array();
		if(count($message))
		{
			$params['m'] = $message;
		}
		return $this->_client->createAppointment($params, $attrs);
	}

	/**
	 * Create Appointment Exception.
	 *
	 * @param  array $message Specification of the message.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	public function createAppointmentException(array $message = array(), array $attrs = array())
	{
		$params = array();
		if(count($message))
		{
			$params['m'] = $message;
		}
		return $this->_client->createAppointmentException($params, $attrs);
	}

	/**
	 * Create a contact.
	 *
	 * @param  array $cn      Contact specification.
	 * @param  array $vcard   Either a vcard or attributes can be specified but not both.
	 * @param  array $members Contact group members. Valid only if the contact being created is a contact group (has attribute type="group").
	 * @param  array $attrs   Attributes.
	 * @param  bool  $verbose If set (defaults to unset) The returned <cn> is just a placeholder containing the new contact ID (i.e. <cn id="{id}"/>).
	 * @return mix
	 */
	public function createContact(array $cn, array $vcard = array(), array $members = array(), array $attrs = array(), $verbose = FALSE)
	{
		$params['cn'] = $cn;
		if(count($vcard))
		{
			$params['cn']['vcard'] = $vcard;
		}
		if(count($members))
		{
			$params['cn']['m'] = $members;
		}
		if(count($attrs))
		{
			$params['cn']['a'] = $attrs;
		}
		return $this->_client->createContact($params, array('verbose' => (bool) $verbose ? 1 : 0 ));
	}

	/**
	 * Creates a data source that imports mail items into the specified folder,
	 * for example via the POP3 or IMAP protocols.
	 * Only one data source is allowed per request.
	 *
	 * @param  string $type      Data source type. Valid values: imap|pop3|caldav|yab|rss|gal|cal|unknown
	 * @param  array  $source    Data source specification.
	 * @param  array  $attrs     Data source attributes.
	 * @param  string $lastError Last error.
	 * @return mix
	 */
	public function createDataSource($type, array $source, array $attrs = array(), $lastError = '')
	{
		$params[$type] = $source;
		if(count($attrs))
		{
			$params[$type]['a'] = $attrs;
		}
		if(!empty($lastError))
		{
			$params[$type]['lastError'] = array('_' => $lastError);
		}
		return $this->_client->createDataSource($params);
	}

	/**
	 * Create folder.
	 *
	 * @param  array $folder New folder specification.
	 * @param  array $grants Action grant selector.
	 * @return mix
	 */
	public function createFolder(array $folder, array $grants = array())
	{
		$params['folder'] = $folder;
		if(count($grants))
		{
			$params['folder']['acl']['grant'] = $grants;
		}
		return $this->_client->createFolder($params);
	}

	/**
	 * Create mountpoint.
	 *
	 * @param  array $link New mountpoint specification.
	 * @return mix
	 */
	public function createMountpoint(array $link)
	{
		$params['link'] = $link;
		return $this->_client->createMountpoint($params);
	}

	/**
	 * Create a note.
	 *
	 * @param  array $note New note specification.
	 * @return mix
	 */
	public function createNote(array $note)
	{
		$params['note'] = $note;
		return $this->_client->createNote($params);
	}

	/**
	 * Create a search folder.
	 *
	 * @param  array $search New search folder specification.
	 * @return mix
	 */
	public function createSearchFolder(array $search)
	{
		$params['search'] = $search;
		return $this->_client->createSearchFolder($params);
	}

	/**
	 * Create a search folder.
	 *
	 * @param  array $tag Tag specification.
	 * @return mix
	 */
	public function createTag(array $tag = array())
	{
		$params = array();
		if(count($tag))
		{
			$params['tag'] = $tag;
		}
		return $this->_client->createTag($params);
	}

	/**
	 * This is the API to create a new Task.
	 *
	 * @param  array $message Specification of the message.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	public function createTask(array $message, array $attrs = array())
	{
		$params = array();
		if(count($message))
		{
			$params['m'] = $message;
		}
		return $this->_client->createTask($params, $attrs);
	}

	/**
	 * Create Task Exception.
	 *
	 * @param  array $message Specification of the message.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	public function createTaskException(array $message, array $attrs = array())
	{
		$params = array();
		if(count($message))
		{
			$params['m'] = $message;
		}
		return $this->_client->createTaskException($params, $attrs);
	}

	/**
	 * Create a waitset to listen for changes on one or more accounts.
	 * Called once to initialize a WaitSet and to set its "default interest types"
	 * WaitSet: scalable mechanism for listening for changes to one or more accounts
	 *
	 * @param  array $add   WaitSet add specification.
	 * @param  array $types Default interest types.
	 * @param  bool  $all   If {all-accounts} is set, then all mailboxes on the system will be listened to, including any mailboxes which are created on the system while the WaitSet is in existence.
	 * @return mix
	 */
	public function createWaitSet(array $add, array $types = array('all'), $all = TRUE)
	{
		$params['add'] = $add;
		$options = array(
			'defTypes' => implode(',', $types),
			'allAccounts' => (bool) $all ? 1 : 0,
		);
		return $this->_client->createWaitSet($params, $attrs);
	}

	/**
	 * Decline a change proposal from an attendee.
	 * Sent by organizer to an attendee who has previously sent a COUNTER message.
	 * The syntax of the request is very similar to CreateAppointmentRequest.
	 *
	 * @param  array $message Details of the Decline Counter. Should have an <inv> which encodes an iCalendar DECLINECOUNTER object.
	 * @return mix
	 */
	public function declineCounterAppointment(array $message)
	{
		$params = array();
		if(count($message))
		{
			$params['m'] = $message;
		}
		return $this->_client->declineCounterAppointment($params);
	}

	/**
	 * Deletes the given data sources.
	 * The name or id of each data source must be specified.
	 *
	 * @param  string $datasources Array of data source.
	 * @return mix
	 */
	public function deleteDataSource(array $datasources = array())
	{
		return $this->_client->deleteDataSource($datasources);
	}

	/**
	 * Permanently deletes mapping for indicated device.
	 *
	 * @param  string $id Device ID.
	 * @return mix
	 */
	public function deleteDevice($id)
	{
		return $this->_client->deleteDevice(array('device' => array('id' => $id)));
	}

	/**
	 * Use this to close out the waitset.
	 * Note that the server will automatically time out a wait set if there is no reference to it for (default of) 20 minutes.
	 * WaitSet: scalable mechanism for listening for changes to one or more accounts.
	 *
	 * @param  string $id Waitset ID.
	 * @return mix
	 */
	public function destroyWaitSet($id)
	{
		return $this->_client->destroyWaitSet(array(), array('waitSet' => $id));
	}

	/**
	 * Performs line by line diff of two revisions of a Document then returns a list of <chunk/> containing the result.
	 * Sections of text that are identical to both versions are indicated with disp="common".
	 * For each conflict the chunk will show disp="first", disp="second" or both.
	 *
	 * @param  string  $id Document ID.
	 * @param  integer $v1 Revision 1.
	 * @param  integer $v2 Revision 2.
	 * @return mix
	 */
	public function diffDocument($id = '', $v1 = 0, $v2 = 0)
	{
		$params = array();
		if(!empty($id))
		{
			$params['doc']['id'] = $id;
			if((int) $v1 > 0) $params['doc']['v1'] = (int) $v1;
			if((int) $v2 > 0) $params['doc']['v2'] = (int) $v2;
		}
		return $this->_client->diffDocument($params);
	}

	/**
	 * Dismiss calendar item alarm.
	 *
	 * @param  array $appt Dismiss appointment alarm.
	 * @param  array $task Dismiss task alarm.
	 * @return mix
	 */
	public function dismissCalendarItemAlarm(array $appt = array(), array $task = array())
	{
		$params = array();
		if(count($appt))
		{
			$params['appt'] = $appt;
		}
		if(count($task))
		{
			$params['task'] = $task;
		}
		return $this->_client->dismissCalendarItemAlarm($params);
	}

	/**
	 * Document action.
	 *
	 * @param  array $action Document action selector. Document specific operations : watch|!watch|grant|!grant.
	 * @param  array $grant  Document action grant.
	 * @return mix
	 */
	public function documentAction(array $action, array $grant = array())
	{
		$params['action'] = $action;
		if(count($grant))
		{
			$params['action']['grant'] = $grant;
		}
		return $this->_client->documentAction($params);
	}

	/**
	 * Empty dumpster.
	 *
	 * @return mix
	 */
	public function emptyDumpster()
	{
		return $this->_client->emptyDumpster($params);
	}

	/**
	 * Enable/disable reminders for shared appointments/tasks on a mountpoint.
	 *
	 * @param  array $id       Mountpoint ID.
	 * @param  bool  $reminder Set to enable (or unset to disable) reminders for shared appointments/tasks.
	 * @return mix
	 */
	public function enableSharedReminder($id, $reminder = TRUE)
	{
		$params['link'] = array(
			'id' => $id,
		);
		if((bool) $reminder)
		{
			$params['link']['reminder'] = 1;
		}
		return $this->_client->enableSharedReminder($params);
	}

	/**
	 * Enable/disable reminders for shared appointments/tasks on a mountpoint.
	 *
	 * @param  array $tz     Timezone definitions.
	 * @param  array $comp   Expanded recurrence invite.
	 * @param  array $except Expanded recurrence exception.
	 * @param  array $cancel Expanded recurrence cancel.
	 * @param  array $attrs  Attributes.
	 * @return mix
	 */
	public function expandRecur(array $tz, array $comp = array(), array $except = array(), array $cancel = array(), array $attrs = array())
	{
		$params = array();
		if(count($tz))
		{
			$params['tz'] = $tz;
		}
		if(count($comp))
		{
			$params['comp'] = $comp;
		}
		if(count($except))
		{
			$params['except'] = $except;
		}
		if(count($cancel))
		{
			$params['cancel'] = $cancel;
		}
		return $this->_client->expandRecur($params, $attrs);
	}

	/**
	 * Export contacts.
	 *
	 * @param  string $ct        Content type. Currently, the only supported content type is "csv" (comma-separated values).
	 * @param  string $folder    Optional folder id to export contacts from.
	 * @param  string $csvfmt    Optional csv format for exported contacts. the supported formats are defined in $ZIMBRA_HOME/conf/zimbra-contact-fields.xml.
	 * @param  string $csvlocale The locale to use when there are multiple {csv-format} locales defined. When it is not specified, the {csv-format} with no locale specification is used.
	 * @param  string $csvsep    Optional delimiter character to use in the resulting csv file - usually "," or ";".
	 * @return mix
	 */
	public function exportContacts($ct, $folder = '', $csvfmt = '', $csvlocale = '', $csvsep = '')
	{
		$options = array(
			'ct' => $ct,
		);
		if(!empty($folder)) $options['l'] = $folder;
		if(!empty($csvfmt)) $options['csvfmt'] = $csvfmt;
		if(!empty($csvlocale)) $options['csvlocale'] = $csvlocale;
		if(!empty($csvsep)) $options['csvsep'] = $csvsep;
		return $this->_client->exportContacts(array(), $options);
	}

	/**
	 * Perform an action on a folder.
	 *
	 * @param  array $action    Select action to perform on folder.
	 * @param  array $grant     Action grant selector.
	 * @param  array $aclGrant  Action acl grant selector.
	 * @param  array $retention Retention policy.
	 * @return mix
	 */
	public function folderAction(array $action, array $grant = array(), array $aclGrant = array(), array $retention = array())
	{
		$params['action'] = $action;
		if(count($grant))
		{
			$params['action']['grant'] = $grant;
		}
		if(count($aclGrant))
		{
			$params['action']['acl']['grant'] = $aclGrant;
		}
		if(count($retention))
		{
			$params['action']['retentionPolicy'] = $retention;
		}
		return $this->_client->folderAction($params);
	}

	/**
	 * Used by an attendee to forward an instance or entire appointment to another user who is not already an attendee.
	 *
	 * @param  string $id       Appointment item ID.
	 * @param  array  $exceptId RECURRENCE-ID information if forwarding a single instance of a recurring appointment.
	 * @param  array  $tz       Definition for TZID referenced by DATETIME in <exceptId>.
	 * @param  array  $message  Details of the appointment.
	 * @return mix
	 */
	public function forwardAppointment($id = '', array $exceptId = array(), array $tz = array(), array $message = array())
	{
		$params = array();
		if(count($exceptId))
		{
			$params['exceptId'] = $exceptId;
		}
		if(count($tz))
		{
			$params['tz'] = $tz;
		}
		if(count($message))
		{
			$params['m'] = $message;
		}

		$options = array();
		if(!empty($id)) $options['id'] = $id;

		return $this->_client->forwardAppointment($params, $options);
	}

	/**
	 * Used by an attendee to forward an appointment invite email to another user who is not already an attendee.
	 * To forward an appointment item, use ForwardAppointmentRequest instead.
	 *
	 * @param  string $id       Invite message item ID.
	 * @param  array  $message  Details of the invite.
	 * @return mix
	 */
	public function forwardAppointmentInvite($id = '', array $message = array())
	{
		$params = array();
		if(count($message))
		{
			$params['m'] = $message;
		}

		$options = array();
		if(!empty($id)) $options['id'] = $id;
		return $this->_client->forwardAppointmentInvite($params, $options);
	}

	/**
	 * Ajax client can use this request to ask the server for help in generating a proper,
	 * globally unique UUID.
	 *
	 * @return mix
	 */
	public function generateUUID()
	{
		return $this->_client->generateUUID();
	}

	/**
	 * Get activity stream.
	 *
	 * @param  string $id Item ID. If the id is for a Document, the response will include the activities for the requested Document. if it is for a Folder, the response will include the activities for all the Documents in the folder and subfolders.
	 * @param  array  $filter  Optionally <filter> can be used to filter the response based on the user that performed the activity, operation, or both. the server will cache previously established filter search results, and return the identifier in session attribute. The client is expected to reuse the session identifier in the subsequent filter search to improve the performance.
	 * @param  int    $limit   Limit - maximum number of activities to be returned
	 * @param  int    $offset  Offset - for getting the next page worth of activities.
	 * @return mix
	 */
	public function getActivityStream($id, array $filter = array(), $limit = 0, $offset = 0)
	{
		$params = array();
		if(count($filter))
		{
			$params['filter'] = $filter;
		}

		$options = array('id' => $id);
		if((int) $limit > 0) $options['limit'] = (int) $limit;
		if((int) $offset > 0) $options['offset'] = (int) $offset;
		return $this->_client->getActivityStream($params, $options);
	}

	/**
	 * Get all devices.
	 *
	 * @return mix
	 */
	public function getAllDevices()
	{
		return $this->_client->getAllDevices();
	}


	/**
	 * Get appointment.
	 * Returns the metadata info for each Invite that makes up this appointment.
	 *
	 * @param  string $id      Appointment ID. Either id or uid should be specified, but not both.
	 * @param  string $uid     iCalendar UID Either id or uid should be specified, but not both.
	 * @param  bool   $sync    Set this to return the modified date (md) on the appointment.
	 * @param  bool   $include If true, MIME parts for body content are returned; default false.
	 * @return mix
	 */
	public function getAppointment($id = '', $uid = '', $sync = TRUE, $include = FALSE)
	{
		$options = array();
		if(!empty($id)) $options['id'] = $id;
		if(!empty($uid)) $options['uid'] = $uid;
		if((bool) $sync) $options['sync'] = 1;
		if((bool) $include) $options['includeContent'] = 1;
		return $this->_client->getAppointment(array(), $options);
	}

	/**
	 * Get appointment summaries.
	 *
	 * @param  int    $start  Range start in milliseconds since the epoch GMT.
	 * @param  int    $end    Range end in milliseconds since the epoch GMT.
	 * @param  string $folder Folder Id. Optional folder to constrain requests to; otherwise, searches all folders but trash and spam.
	 * @return mix
	 */
	public function getApptSummaries($start, $end, $folder = '')
	{
		$options = array(
			's' => (int) $start,
			'e' => (int) $end,
		);
		if(!empty($folder)) $options['l'] = $folder;
		return $this->_client->getApptSummaries(array(), $options);
	}

	/**
	 * Get Calendar item summaries.
	 *
	 * @param  int    $start  Range start in milliseconds since the epoch GMT.
	 * @param  int    $end    Range end in milliseconds since the epoch GMT.
	 * @param  string $folder Folder Id.
	 * @return mix
	 */
	public function getCalendarItemSummaries($start, $end, $folder = '')
	{
		$options = array(
			's' => (int) $start,
			'e' => (int) $end,
		);
		if(!empty($folder)) $options['l'] = $folder;
		return $this->_client->getCalendarItemSummaries(array(), $options);
	}

	/**
	 * Get comments.
	 *
	 * @param  string $parentId Item ID of parent.
	 * @return mix
	 */
	public function getComments($parentId)
	{
		$params['comment'] = array('$parentId' => $parentId);
		return $this->_client->getComments($params);
	}

	/**
	 * Get contacts.
	 * Contact group members are returned as <m> elements.
	 * If derefGroupMember is not set, group members are returned in the order they were inserted in the group.
	 * If derefGroupMember is set, group members are returned ordered by the "key" of member.
	 * Key is:
	 *   1. for contact ref (type="C"): the fileAs field of the Contact
	 *   2. for GAL ref (type="G"): email address of the GAL entry
	 *   3. for inlined member (type="I"): the value
	 *
	 * @param  bool   $sync   If set, return modified date (md) on contacts.
	 * @param  string $folder If is present, return only contacts in the specified folder.
	 * @param  string $sort   Sort by.
	 * @param  bool   $deref  If set, deref contact group members.
	 * @param  bool   $hidden Whether to return contact hidden attrs defined in zimbraContactHiddenAttributes ignored if <a> is present..
	 * @param  int    $max    Max members.
	 * @param  array  $attrs  Attributes - if present, return only the specified attribute(s).
	 * @param  array  $ma     If present, return only the specified attribute(s) for derefed members, applicable only when derefGroupMember is set.
	 * @param  array  $cn     If present, only get the specified contact(s)..
	 * @return mix
	 */
	public function getContacts($sync = FALSE, $folder = '', $sort = '', $deref = FALSE, $hidden = FALSE, $max = 0, array $attrs = array(), array $ma = array(), array $cn = array())
	{
		$params = array();
		if(count($filter))
		{
			$params['filter'] = $filter;
		}
		$attributes = $this->_attributes($attrs);
		if(count($attributes))
		{
			$params['a'] = $attributes;			
		}
		$attributes = $this->_attributes($ma);
		if(count($ma))
		{
			$params['ma'] = $attributes;			
		}
		if(count($cn))
		{
			$params['cn'] = array();
			foreach ($cn as $key => $value)
			{
				$params['cn'][] = array($id => $value);
			}
		}

		$options = array();
		if((bool) $sync) $options['sync'] = 1;
		if(!empty($folder)) $options['l'] = $folder;
		if(!empty($sort)) $options['sortBy'] = $sort;
		if((bool) $deref) $options['derefGroupMember'] = 1;
		if((bool) $hidden) $options['returnHiddenAttrs'] = 1;
		if((int) $max > 0) $options['maxMembers'] = (int) $max;
		return $this->_client->getContacts($params, $options);
	}

	/**
	 * Get conversation.
	 * GetConvRequest gets information about the 1 conversation named by id's value.
	 * It will return exactly 1 conversation element. 
	 * If fetch="1|all" is included,
	 * the full expanded message structure is inlined for the first (or for all) messages in the conversation.
	 * If fetch="{item-id}", only the message with the given {item-id} is expanded inline
	 *
	 * @param  string $id      Conversation ID.
	 * @param  string $fetch   If value is "1" or "all" the full expanded message structure is inlined for the first (or for all) messages in the conversation. If fetch="{item-id}", only the message with the given {item-id} is expanded inline
	 * @param  bool   $html    Set to return defanged HTML content by default. (default is unset).
	 * @param  int    $max     Maximum inlined length.
	 * @param  array  $headers Requested headers. if <header>s are requested, any matching headers are inlined into the response (not available when raw is set).
	 * @return mix
	 */
	public function getConv($id, $fetch = '', $html = FALSE, $max = 0, array $headers = array())
	{
		$params['c'] = array(
			'id' => $id
		);
		if(!empty($fetch))
		{
			$params['c']['fetch'] = $fetch;
		}
		if((bool) $html)
		{
			$params['c']['html'] = 1;			
		}
		if(intval($max))
		{
			$params['c']['max'] = (int) $max;			
		}
		$attributes = $this->_attributes($headers);
		if(count($headers))
		{
			$params['c']['headers'] = $attributes;
		}
		return $this->_client->getConv($params);
	}

	/**
	 * Get custom metadata.
	 *
	 * @param  string $id      Item ID.
	 * @param  string $section Metadata section key.
	 * @return mix
	 */
	public function getCustomMetadata($id, $section = '')
	{
		$params = array();
		if(!empty($section))
		{
			$params['section'] = $section;
		}
		return $this->_client->getCustomMetadata($params, array('id' => $id));
	}

	/**
	 * Returns all data sources defined for the given mailbox.
	 * For each data source, every attribute value is returned except password.
	 *
	 * @return mix
	 */
	public function getDataSources()
	{
		return $this->_client->getDataSources();
	}

	/**
	 * Get the download URL of shared document.
	 *
	 * @param  string $id     Item ID.
	 * @param  string $folder Folder ID.
	 * @param  string $name   Name.
	 * @param  string $path   Fully qualified path.
	 * @return mix
	 */
	public function getDocumentShareURL($id = '', $folder = '', $name = '', $path = '')
	{
		$params = array();
		$item = array();
		if(!empty($id)) $item['id'] = $id;
		if(!empty($folder)) $item['l'] = $folder;
		if(!empty($name)) $item['name'] = $name;
		if(!empty($path)) $item['path'] = $path;
		if(count($item)) $params['item'] = $item;
		return $this->_client->getDocumentShareURL($params);
	}

	/**
	 * Returns the effective permissions of the specified folder.
	 *
	 * @param  string $folder Folder ID.
	 * @return mix
	 */
	public function getEffectiveFolderPerms($folder)
	{
		$params['folder'] = array('l' => $folder);
		return $this->_client->getEffectiveFolderPerms($params);
	}

	/**
	 * Get filter rules.
	 *
	 * @return mix
	 */
	public function getFilterRules()
	{
		return $this->_client->getFilterRules();
	}

	/**
	 * Get folder.
	 * A {base-folder-id}, a {base-folder-uuid} or a {fully-qualified-path} can optionally be specified in the folder element; if none is present, the descent of the folder hierarchy begins at the mailbox's root folder (id 1).
	 * If {fully-qualified-path} is present and {base-folder-id} or {base-folder-uuid} is also present, the path is treated as relative to the folder that was specified by id/uuid. {base-folder-id} is ignored if {base-folder-uuid} is present.
	 *
	 * @param  string $uuid    Base folder UUID.
	 * @param  string $folder  Base folder ID.
	 * @param  string $path    Fully qualified path.
	 * @param  string $visible If set we include all visible subfolders of the specified folder. When you have full rights on the mailbox, this is indistinguishable from the normal <GetFolderResponse>.
	 * @param  string $grant   If set then grantee names are supplied in the d attribute in <grant>. Default: unset.
	 * @param  string $view    If "view" is set then only the folders with matching view will be returned. Otherwise folders with any default views will be returned.
	 * @param  string $depth   If "depth" is set to a non-negative number, we include that many levels of subfolders in the response. (so if depth="1", we'll include only the folder and its direct subfolders) If depth is missing or negative, the entire folder hierarchy is returned.
	 * @param  string $tr      If true, one level of mountpoints are traversed and the target folder's counts are applied to the local mountpoint. if the root folder as referenced by {base-folder-id} and/or {fully-qualified-path} is a mountpoint, "tr" is regarded as being automatically set. Mountpoints under mountpoints are not themselves expanded.
	 * @return mix
	 */
	public function getFolder($uuid = '', $folder = '', $path = '', $visible = FALSE, $grant = FALSE, $view = '', $depth = 0, $tr = FALSE)
	{
		$params = array();
		$folder = array();
		if(!empty($uuid)) $folder['uuid'] = $uuid;
		if(!empty($folder)) $folder['l'] = $folder;
		if(!empty($path)) $folder['path'] = $path;
		if(count($folder)) $params['folder'] = $folder;

		$options = array();
		if((bool) $visible) $options['visible'] = 1;
		if((bool) $grant) $options['needGranteeName'] = 1;
		if(!empty($view)) $options['view'] = $view;
		if((int) $depth > 0) $options['depth'] = (int) $depth;
		if((bool) $tr) $options['tr'] = 1;
		return $this->_client->getFolder($params, $options);
	}

	/**
	 * Get Free/Busy information.
	 * For accounts listed using uid,id or name attributes, f/b search will be done for all calendar folders. 
	 * To view free/busy for a single folder in a particular account, use <usr>.
	 *
	 * @param  int    $start   Range start in milliseconds.
	 * @param  int    $end     Range end in milliseconds.
	 * @param  array  $ids     Array of Zimbra IDs.
	 * @param  array  $names   Array of Emails.
	 * @param  string $exclude UID of appointment to exclude from free/busy search.
	 * @param  array  $usrs    To view free/busy for a single folders in particular accounts, use these.
	 * @return mix
	 */
	public function getFreeBusy($start, $end, array $ids = array(), array $names = array(), $exclude = '', array $usrs = array())
	{
		$params = array();
		if(count($usrs)) $params['usr'] = $usrs;

		$options = array(
			's' => (int) $start,
			'e' => (int) $end,
		);
		$id = $this->_commaAttributes($ids);
		if(!empty($id)) $options['id'] = $id;
		$name = $this->_commaAttributes($names);
		if(!empty($name)) $options['name'] = $name;
		if(!empty($exclude)) $options['excludeUid'] = $exclude;
		return $this->_client->getFreeBusy($params, $options);
	}

	/**
	 * Retrieve the unparsed (but XML-encoded (&quot)) iCalendar data for an Invite.
	 * This is intended for interfacing with 3rd party programs. 
	 *   1. If id attribute specified, gets the iCalendar representation for one invite.
	 *   1. If id attribute is not specified, then start/end MUST be, Calendar data is returned for entire specified range.
	 *
	 * @param  string $id    If specified, gets the iCalendar representation for one invite.
	 * @param  int    $start Range start in milliseconds.
	 * @param  int    $end   Range end in milliseconds.
	 * @return mix
	 */
	public function getICal($id = '', $start = 0, $end = 0)
	{
		$options = array();
		if(!empty($id)) $options['id'] = $id;
		if((int) $start > 0) $options['s'] = (int) $start;
		if((int) $end > 0) $options['e'] = (int) $end;
		return $this->_client->getICal(array(), $options);
	}

	/**
	 * Returns current import status for all data sources.
	 * Status values for a data source are reinitialized when either (a) another
	 * import process is started or (b) when the server is restarted.
	 * If import has not run yet, the success and error attributes are not specified in the response.
	 *
	 * @return mix
	 */
	public function getImportStatus()
	{
		return $this->_client->getImportStatus();
	}

	/**
	 * Get item.
	 * A successful GetItemResponse will contain a single element appropriate for the type of
	 * the requested item if there is no matching item, a fault containing the code mail.
	 * NO_SUCH_ITEM is returned
	 *
	 * @param  string $id     Item ID.
	 * @param  string $folder Folder ID.
	 * @param  string $name   Name.
	 * @param  string $path   Fully qualified path.
	 * @return mix
	 */
	public function getItem($id = '', $folder = '', $name = '', $path = '')
	{
		$params = array();
		$item = array();
		if(!empty($id)) $item['id'] = $id;
		if(!empty($folder)) $item['l'] = $folder;
		if(!empty($name)) $item['name'] = $name;
		if(!empty($path)) $item['path'] = $path;
		if(count($item)) $params['item'] = $item;
		return $this->_client->getItem($params);
	}

	/**
	 * Get Mailbox metadata.
	 *
	 * @param  string $section Metadata section key.
	 * @return mix
	 */
	public function getMailboxMetadata($section = '')
	{
		$params = array();
		$meta = array();
		if(!empty($section)) $meta['section'] = $section;
		if(count($meta)) $params['meta'] = $meta;
		return $this->_client->getMailboxMetadata($params);
	}

	/**
	 * Get information needed for Mini Calendar.
	 * Date is returned if there is at least one appointment on that date.
	 * The date computation uses the requesting (authenticated) account's time zone,
	 * not the time zone of the account that owns the calendar folder.
	 *
	 * @param  int   $start    Range start time in milliseconds.
	 * @param  int   $end      Metadata section key.
	 * @param  array $folders  Local and/or remote calendar folders.
	 * @param  array $tz       Optional timezone specifier. References an existing server-known timezone by ID or the full specification of a custom timezone.
	 * @param  array $standard Time/rule for transitioning from daylight time to standard time. Either specify week/wkday combo, or mday.
	 * @param  array $daylight Time/rule for transitioning from daylight time to standard time. Either specify week/wkday combo, or mday.
	 * @return mix
	 */
	public function getMiniCal($start, $end, array $folders = array(), array $tz = array(), array $standard = array(), array $daylight = array())
	{
		$params = array();
		if(count($folders))
		{
			$params['folder'] = array();
			foreach ($folders as $key => $value)
			{
				$params['folder'][] = array('id' => $value);
			}
		}
		if(count($tz))
		{
			$params['tz'] = $tz;
		}
		if(count($standard))
		{
			$params['tz']['standard'] = $standard;
		}
		if(count($daylight))
		{
			$params['tz']['daylight'] = $daylight;
		}

		$options = array(
			's' => (int) $start,
			'e' => (int) $end,
		);
		return $this->_client->getMiniCal($params, $options);
	}

	/**
	 * Get message.
	 *
	 * @param  array $message Message specification.
	 * @return mix
	 */
	public function getMsg(array $message)
	{
		$params = array(
			'm' => $message
		);
		return $this->_client->getMsg($params);
	}

	/**
	 * Get message metadata.
	 *
	 * @param  array $ids Array of message ID selector.
	 * @return mix
	 */
	public function getMsgMetadata(array $ids)
	{
		$listID = $this->_commaAttributes($ids);
		$params = array(
			'm' => array(
				'ids' => $listID
			)
		);
		return $this->_client->getMsgMetadata($params);
	}

	/**
	 * Get note.
	 *
	 * @param  int $id Note ID.
	 * @return mix
	 */
	public function getNote($id)
	{
		$params = array(
			'note' => array(
				'id' => $id
			)
		);
		return $this->_client->getNote($params);
	}

	/**
	 * Get notifications.
	 *
	 * @param  bool $markSeen If set then all the notifications will be marked as seen. Default: unset.
	 * @return mix
	 */
	public function getNotifications($markSeen = FALSE)
	{
		$options = array(
			'markSeen' => (bool) $markSeen ? 1 : 0,
		);
		return $this->_client->getNotifications(array(), $options);
	}

	/**
	 * Get outgoing filter rules.
	 *
	 * @return mix
	 */
	public function getOutgoingFilterRules()
	{
		return $this->_client->getOutgoingFilterRules();
	}

	/**
	 * Get account level permissions.
	 * If no <ace> elements are provided, all ACEs are returned in the response. 
	 * If <ace> elements are provided, only those ACEs with specified rights are returned in the response.
	 *
	 * @param  array $rights Specification of rights.
	 * @return mix
	 */
	public function getPermission(array $rights = array())
	{
		$params = array();
		if(count($rights))
		{
			$params['ace'] = array();
			foreach ($rights as $right)
			{
				$params['ace'][] = array('right' => $right);
			}
		}
		return $this->_client->getPermission($params);
	}

	/**
	 * Retrieve the recurrence definition of an appointment.
	 *
	 * @param  string $id Calendar item ID.
	 * @return mix
	 */
	public function getRecur($id)
	{
		return $this->_client->getRecur(array(), array('id' => $id));
	}

	/**
	 * Get all search folders.
	 *
	 * @return mix
	 */
	public function getSearchFolder()
	{
		return $this->_client->getSearchFolder();
	}

	/**
	 * Get item acl details.
	 *
	 * @param  string $id Item ID.
	 * @return mix
	 */
	public function getShareDetails($id = '')
	{
		$params = array();
		if(!empty($id))
		{
			$params['item'] = array('id' => $id);
		}
		return $this->_client->getShareDetails($params);
	}

	/**
	 * Get Share notifications.
	 *
	 * @return mix
	 */
	public function getShareNotifications()
	{
		return $this->_client->getShareNotifications();
	}

	/**
	 * GetReturns the list of dictionaries that can be used for spell checking.
	 *
	 * @return mix
	 */
	public function getSpellDictionaries()
	{
		return $this->_client->getSpellDictionaries();
	}

	/**
	 * Get system retention policy.
	 *
	 * @return mix
	 */
	public function getSystemRetentionPolicy()
	{
		return $this->_client->getSystemRetentionPolicy();
	}

	/**
	 * Get information about Tags.
	 *
	 * @return mix
	 */
	public function getTag()
	{
		return $this->_client->getTag();
	}

	/**
	 * Get Task.
	 * Similar to GetAppointmentRequest/GetAppointmentResponse
	 *
	 * @param  string $id      Appointment ID. Either id or uid should be specified, but not both.
	 * @param  string $uid     iCalendar UID Either id or uid should be specified, but not both.
	 * @param  bool   $sync    Set this to return the modified date (md) on the appointment..
	 * @param  bool   $include If set, MIME parts for body content are returned; default false.
	 * @return mix
	 */
	public function getTask($id = '', $uid = '', $sync = TRUE, $include = FALSE)
	{
		$options = array();
		if(!empty($id)) $options['id'] = $id;
		if(!empty($uid)) $options['uid'] = $uid;
		if((bool) $sync) $options['sync'] = 1;
		if((bool) $include) $options['includeContent'] = 1;
		return $this->_client->getTask(array(), $options);
	}

	/**
	 * Get task summaries.
	 *
	 * @param  int    $start  Range start in milliseconds since the epoch GMT.
	 * @param  int    $end    Range end in milliseconds since the epoch GMT.
	 * @param  string $folder Folder Id. Optional folder to constrain requests to; otherwise, searches all folders but trash and spam.
	 * @return mix
	 */
	public function getTaskSummaries($start, $end, $folder = '')
	{
		$options = array(
			's' => (int) $start,
			'e' => (int) $end,
		);
		if(!empty($folder)) $options['l'] = $folder;
		return $this->_client->getTaskSummaries(array(), $options);
	}

	/**
	 * Returns a list of items in the user's mailbox currently being watched by other users.
	 *
	 * @return mix
	 */
	public function getWatchers()
	{
		return $this->_client->getWatchers();
	}

	/**
	 * Returns a list of items the user is currently watching.
	 *
	 * @return mix
	 */
	public function getWatchingItems()
	{
		return $this->_client->getWatchingItems();
	}

	/**
	 * User's working hours within the given time range are expressed in a similar format to the format used for GetFreeBusy.
	 * Working hours are indicated as free, non-working hours as unavailable/out of office.
	 * The entire time range is marked as unknown if there was an error determining the working hours, e.g. unknown user.
	 *
	 * @param  int   $start Range start in milliseconds since the epoch.
	 * @param  int   $end   Range end in milliseconds since the epoch.
	 * @param  array $ids   Array of Zimbra IDs.
	 * @param  array $emails Array of email addresses.
	 * @return mix
	 */
	public function getWorkingHours($start, $end, array $ids = array(), array $emails = array())
	{
		$options = array(
			's' => (int) $start,
			'e' => (int) $end,
		);
		$id = $this->_commaAttributes($ids);
		if(!empty($id)) $options['id'] = $id;
		$email = $this->_commaAttributes($emails);
		if(!empty($email)) $options['name'] = $email;
		return $this->_client->getWorkingHours(array(), $options);
	}

	/**
	 * Get Yahoo Auth Token.
	 *
	 * @param  string $user     Yahoo user.
	 * @param  string $password Yahoo user password.
	 * @return mix
	 */
	public function getYahooAuthToken($user, $password)
	{
		$options = array(
			'user' => $user,
			'password' => $password,
		);
		return $this->_client->getYahooAuthToken(array(), $options);
	}

	/**
	 * Get Yahoo cookie.
	 *
	 * @param  string $user Yahoo user.
	 * @return mix
	 */
	public function getYahooCookie($user)
	{
		return $this->_client->getYahooCookie();
	}

	/**
	 * Grant account level permissions.
	 * GrantPermissionResponse returns permissions that are successfully granted.
	 *
	 * @param  array $ace Specify Access Control Entries (ACEs).
	 * @return mix
	 */
	public function grantPermission(array $ace = array())
	{
		$params = array();
		if(count($ace))
		{
			$params['ace'] = $ace;
		}
		return $this->_client->grantPermission($params);
	}

	/**
	 * Do an iCalendar Reply.
	 *
	 * @param  string $ical iCalendar text containing components with method REPLY.
	 * @return mix
	 */
	public function iCalReply($ical)
	{
		$params = array('ical' => $ical);
		return $this->_client->iCalReply($params);
	}

	/**
	 * Import appointments.
	 *
	 * @param  string $ct     Content type. Only currently supported content type is "text/calendar" (and its nickname "ics").
	 * @param  string $aid    Attachment upload ID of uploaded object to use.
	 * @param  string $mid    Message ID of existing message. Used in conjunction with "part".
	 * @param  string $part   Part identifier. This combined with "mid" identifies a part of an existing message.
	 * @param  string $folder Optional folder ID to import appointments into.
	 * @return mix
	 */
	public function importAppointments($ct, $aid = '', $mid = '', $part = '', $folder = '')
	{
		$options = array('ct' => $ct);
		if(!empty($folder)) $options['l'] = $folder;
		$params['content'] = array();
		if(!empty($aid)) $params['content']['aid'] = $aid;
		if(!empty($mid)) $params['content']['mid'] = $mid;
		if(!empty($part)) $params['content']['part'] = $part;
		return $this->_client->importAppointments($params, $options);
	}

	/**
	 * Import appointments.
	 *
	 * @param  string $ct        Content type. Only currenctly supported content type is "csv".
	 * @param  string $aid       Attachment upload ID of uploaded object to use.
	 * @param  string $csvfmt    The format of csv being imported. when it's not defined, Zimbra format is assumed. the supported formats are defined in $ZIMBRA_HOME/conf/zimbra-contact-fields.xml.
	 * @param  string $csvlocale The locale to use when there are multiple {csv-format} locales defined. When it is not specified, the {csv-format} with no locale specification is used.
	 * @param  string $folder    Optional Folder ID to import contacts to.
	 * @return mix
	 */
	public function importContacts($ct, $aid = '', $csvfmt = '', $csvlocale = '', $folder = '')
	{
		$options = array('ct' => $ct);
		if(!empty($folder)) $options['l'] = $folder;
		if(!empty($csvfmt)) $options['csvfmt'] = $csvfmt;
		if(!empty($csvlocale)) $options['csvlocale'] = $csvlocale;
		$content['content'] = array();
		if(!empty($aid)) $params['content']['aid'] = $aid;
		return $this->_client->importContacts($params, $options);
	}

	/**
	 * Triggers the specified data sources to kick off their import processes.
	 * Data import runs asynchronously, so the response immediately returns.
	 * Status of an import can be queried via the <GetImportStatusRequest> message.
	 * If the server receives an <ImportDataRequest> while an import is already running
	 * for a given data source, the second request is ignored.
	 *
	 * @param  string $type        Data source type.
	 * @param  array  $datasource  Data source.
	 * @return mix
	 */
	public function importData($type, array $datasource = array())
	{
		$params = array($type => $datasource);
		return $this->_client->importData($params);
	}

	/**
	 * Invalidate reminder device.
	 *
	 * @param  string $device Device email address.
	 * @return mix
	 */
	public function invalidateReminderDevice($device)
	{
		return $this->_client->invalidateReminderDevice(array(), array('a' => $device));
	}

	/**
	 * Perform an action on an item.
	 *
	 * @param  array $action Specify the action to perform.
	 * @return mix
	 */
	public function itemAction(array $action)
	{
		return $this->_client->itemAction(array('action' => $action));
	}

	/**
	 * Returns {num} number of revisions starting from {version} of the requested document.
	 * {num} defaults to 1. {version} defaults to the current version.
	 * Documents that have multiple revisions have the flag "/", which indicates that the document is versioned.
	 *
	 * @param  string $id     Item ID.
	 * @param  int    $ver   Version.
	 * @param  int    $count Maximum number of revisions to return starting from {version}.
	 * @return mix
	 */
	public function listDocumentRevisions($id, $ver = 0, $count = 0)
	{
		$params = array('doc' => array(
			'id' => $id
		));
		if((int) $ver > 0) $params['doc']['ver'] = (int) $ver;
		if((int) $count > 0) $params['doc']['count'] = (int) $count;
		return $this->_client->listDocumentRevisions($params);
	}

	/**
	 * Modify an appointment, or if the appointment is a recurrence then modify the "default" invites.
	 * That is, all instances that do not have exceptions. .
	 * If the appointment has a <recur>, then the following caveats are worth mentioning:.
	 * If any of: START, DURATION, END or RECUR change, then all exceptions are implicitly canceled!.
	 *
	 * @param  array $message Specification of the message.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	public function modifyAppointment(array $message = array(), array $attrs = array())
	{
		$params = array();
		if(count($message)) $params['m'] = $message;
		return $this->_client->modifyAppointment($params, $attrs);
	}

	/**
	 * Modify Contact.
	 * When modifying tags, all specified tags are set and all others are unset.
	 * If tn="{tag-names}" is NOT specified then any existing tags will remain set.
	 *
	 * @param  array $cn      Contact specification.
	 * @param  array $members Contact group members. Valid only if the contact being created is a contact group (has attribute type="group").
	 * @param  array $attrs   Attributes.
	 * @param  bool  $replace If set, all attrs and group members in the specified contact are replaced with specified attrs and group members, otherwise the attrs and group members are merged with the existing contact. Unset by default.
	 * @param  bool  $verbose If set (defaults to unset) The returned <cn> is just a placeholder containing the new contact ID (i.e. <cn id="{id}"/>).
	 * @return mix
	 */
	public function modifyContact(array $cn, array $members = array(), array $attrs = array(), $replace = FALSE, $verbose = FALSE)
	{
		$params['cn'] = $cn;
		if(count($members)) $params['m'] = $members;
		if(count($attrs)) $params['a'] = $attrs;

		$options = array();
		if((bool) $replace) $options['replace'] = 1;
		if((bool) $verbose) $options['verbose'] = 1;
		return $this->_client->modifyContact($params, $options);
	}

	/**
	 * Changes attributes of the given data source.
	 * Only the attributes specified in the request are modified.
	 * If the username, host or leaveOnServer settings are modified,
	 * the server wipes out saved state for this data source.
	 * As a result, any previously downloaded messages that are still stored
	 * on the remote server will be downloaded again.
	 *
	 * @param  string $type      Data source type. Valid values: imap|pop3|caldav|yab|rss|gal|cal|unknown
	 * @param  array  $source    Data source specification.
	 * @param  array  $attrs     Data source attributes.
	 * @param  string $lastError Last error.
	 * @return mix
	 */
	public function modifyDataSource($type, array $source, array $attrs = array(), $lastError = '')
	{
		$params[$type] = $source;
		if(count($attrs)) $params[$type]['a'] = $attrs;
		if(!empty($lastError)) $params[$type]['lastError'] = $lastError;
		return $this->_client->modifyDataSource($params);
	}

	/**
	 * Modify Filter rules.
	 *
	 * @param  array $rules Filter rules.
	 * @return mix
	 */
	public function modifyFilterRules(array $rules)
	{
		$params['filterRules'] = $rules;
		return $this->_client->modifyFilterRules($params);
	}

	/**
	 * AppliesModify Mailbox Metadata.
	 *   1. Modify request must contain one or more key/value pairs.
	 *   2. Existing keys' values will be replaced by new values
	 *   3. Empty or null value will remove a key
	 *   4. New keys can be added
	 *
	 * @param  string $section Section. Normally present. If absent this indicates that CustomMetadata info is present but there are no sections to report on.
	 * @param  array  $attrs   Attributes. Key value pairs.
	 * @return mix
	 */
	public function modifyMailboxMetadata($section = '', array $attrs = array())
	{
		$params = array();
		$meta = array();
		if(!empty($section)) $meta['section'] = $section;
		$attributes = $this->_attributes($attrs);
		if(count($attributes)) $meta['a'] = $attributes;
		if(count($meta)) $params['meta'] = $meta;
		return $this->_client->modifyMailboxMetadata($params);
	}

	/**
	 * Modify Outgoing Filter rules.
	 *
	 * @param  array $rules Filter rules.
	 * @return mix
	 */
	public function modifyOutgoingFilterRules(array $rules)
	{
		$params['filterRules'] = $rules;
		return $this->_client->modifyOutgoingFilterRules($params);
	}

	/**
	 * Modify Search Folder.
	 *
	 * @param  string $id     Search ID.
	 * @param  string $query  Search query.
	 * @param  string $types  Search types.
	 * @param  string $sortBy Sort by.
	 * @return mix
	 */
	public function modifySearchFolder($id, $query, $types = '', $sortBy = '')
	{
		$params['search'] = array(
			'id' => $id,
			'query' => $query,
		);
		if(!empty($types)) $params['types'] = $types;
		if(!empty($sortBy)) $params['sortBy'] = $sortBy;
		return $this->_client->modifySearchFolder($params);
	}

	/**
	 * Modify Task.
	 *
	 * @param  array $message Specification of the message.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	public function modifyTask(array $message, array $attrs = array())
	{
		$params['m'] = $message;
		return $this->_client->modifyTask($params, $attrs);
	}
	/**
	 * Perform an action on a message.
	 * For op="update", caller can specify any or all of: l="{folder}", name="{name}", color="{color}", tn="{tag-names}", f="{flags}". 
	 * For op="!spam", can optionally specify a destination folder
	 *
	 * @param  string $op     Operation.
	 * @param  string $tcon   List of characters; constrains the set of affected items in a conversation.
	 * @param  string $folder Folder ID.
	 * @param  string $rgb    RGB color in format #rrggbb where r,g and b are hex digits.
	 * @param  string $color  Color numeric; range 0-127; defaults to 0 if not present; client can display only 0-7.
	 * @param  string $name   Name.
	 * @param  string $flag   Flags.
	 * @param  array  $ids    Array of item IDs to act on. Required except for TagActionRequest, where the tags items can be specified using their tag names as an alternative.
	 * @param  array  $tag    Array of tag names.
	 * @return mix
	 */
	public function msgAction($op, $tcon = '', $folder = '', $rgb = '', $color = '', $name = '', $flag = '', array $ids = array(), array $tags = array())
	{
		$params['action'] = array(
			'op' => $op,
		);
		if(!empty($tcon)) $params['action']['tcon'] = $tcon;
		if(!empty($folder)) $params['action']['l'] = $folder;
		if(!empty($rgb)) $params['action']['rgb'] = $rgb;
		if(!empty($color)) $params['action']['color'] = $color;
		if(!empty($name)) $params['action']['name'] = $name;
		if(!empty($flag)) $params['action']['f'] = $flag;
		$id = $this->_commaAttributes($ids);
		if(!empty($id)) $params['action']['id'] = $id;
		$tn = $this->_commaAttributes($tag);
		if(!empty($tn)) $params['action']['tn'] = $tn;

		return $this->_client->msgAction($params);
	}

	/**
	 * A request that does nothing and always returns nothing.
	 * Used to keep a session alive, and return any pending notifications.
	 *
	 * If "wait" is set, and if the current session allows them, this request will block until there are new notifications for the client.
	 * Note that the soap envelope must reference an existing session that has notifications enabled, and the notification sequencing number should be specified.
	 *
	 * If "wait" is set, the caller can specify whether notifications on delegate sessions will cause the operation to return.
	 * If "delegate" is unset, delegate mailbox notifications will be ignored. "delegate" is set by default. 
	 *
	 * @param  bool $wait     Wait setting.
	 * @param  bool $delegate If "wait" is set, the caller can use this setting to determine whether notifications on delegate sessions will cause the operation to return. If "delegate" is unset, delegate mailbox notifications will be ignored. "delegate" is set by default.
	 * @param  bool $limit    If specified, the server will only allow a given user to have one single waiting-NoOp on the server at a time, it will complete (with waitDisallowed set) any existing limited hanging NoOpRequests when a new request comes in.
	 * @param  int  $timeout  The client may specify a custom timeout-length for their request if they know something about the particular underlying network. The server may or may not honor this request (depending on server configured max/min values: see LocalConfig variables zimbra_noop_default_timeout, zimbra_noop_min_timeout and zimbra_noop_max_timeout).
	 * @return mix
	 */
	public function noOp($wait = FALSE, $delegate = FALSE, $limit = FALSE, $timeout = 0)
	{
		$options = array();
		if((bool) $wait) $options['wait'] = 1;
		if((bool) $delegate) $options['delegate'] = 1;
		if((bool) $limit) $options['limitToOneBlocked'] = 1;
		if((int) $limit) $options['timeout'] = $timeout;
		return $this->_client->noOp(array(), $options);
	}

	/**
	 * Perform an action on an note.
	 *
	 * @param  array $action Specify the action to perform.
	 * @return mix
	 */
	public function noteAction(array $action)
	{
		$params['action'] = $action;
		return $this->_client->noteAction($params);
	}

	/**
	 * Purge revision.
	 *
	 * @param  string $id      Item ID.
	 * @param  int    $ver     Revision.
	 * @param  bool   $include When set, the server will purge all the old revisions inclusive of the revision specified in the request.
	 * @return mix
	 */
	public function purgeRevision($id, $ver, $include = FALSE)
	{
		$params['revision'] = array(
			'id' => $id,
			'ver' => (int) $ver,
		);
		if((bool) $include) $params['revision']['includeOlderRevisions'] = 1;
		return $this->_client->purgeRevision($params);
	}

	/**
	 * Perform an action on the contact ranking table.
	 *
	 * @param  string $op    Action to perform - reset|delete.
	 * @param  string $email Email address. Required if action is "delete".
	 * @return mix
	 */
	public function rankingAction($op, $email = '')
	{
		$params['action'] = array(
			'op' => $op,
		);
		if(!empty($email)) $params['action']['email'] = $email;
		return $this->_client->rankingAction($params);
	}

	/**
	 * Register a device.
	 *
	 * @param  string $name Device name.
	 * @return mix
	 */
	public function registerDevice($name)
	{
		$params['device'] = array(
			'name' => $name,
		);
		return $this->_client->registerDevice($params);
	}

	/**
	 * Remove attachments from a message body.
	 * NOTE that this operation is effectively a create and a delete, and thus the message's item ID will change.
	 *
	 * @param  string $id    Message ID.
	 * @param  array  $parts Array of part IDs to remove.
	 * @return mix
	 */
	public function removeAttachments($id, array $parts)
	{
		$params['m'] = array(
			'id' => $id,
			'parts' => $this->_commaAttributes($parts),
		);
		return $this->_client->removeAttachments($params);
	}

	/**
	 * Revoke account level permissions.
	 * RevokePermissionResponse returns permissions that are successfully revoked.
	 *
	 * @param  array $ace Specify Access Control Entries (ACEs).
	 * @return mix
	 */
	public function revokePermission(array $ace = array())
	{
		$params = array();
		if(count($ace)) $params['ace'] = $ace;
		return $this->_client->revokePermission($params);
	}

	/**
	 * Save Document.
	 * One mechanism for Creating and updating a Document is:
	 *   1. Use FileUploadServlet to upload the document.
	 *   1. Call SaveDocumentRequest using the upload-id returned from FileUploadServlet.
	 * A Document represents a file.
	 * A file can be created by uploading to FileUploadServlet.
	 * Or it can refer to an attachment of an existing message.
	 *
	 * Documents are versioned.
	 * The server maintains the metadata of each version, such as by who and when the version was edited, and the fragment. 
	 *
	 * @param  array $doc Document specification.
	 * @return mix
	 */
	public function saveDocument(array $doc)
	{
		return $this->_client->saveDocument(array('doc' => $doc));
	}

	/**
	 * Save draft.
	 *   1. Only allowed one top-level <mp> but can nest <mp>s within if multipart/* on reply/forward. Set origid on <m> element and set rt to "r" or "w", respectively.
	 *   2. Can optionally set identity-id to specify the identity being used to compose the message. If updating an existing draft, set "id" attr on <m> element.
	 *   3. Can refer to parts of existing draft in <attach> block.
	 *   4. Drafts default to the Drafts folder.
	 *   5. Setting folder/tags/flags/color occurs after the draft is created/updated, and if it fails the content WILL STILL BE SAVED.
	 *   6. Can optionally set autoSendTime to specify the time at which the draft should be automatically sent by the server.
	 *   7. The ID of the saved draft is returned in the "id" attribute of the response.
	 *   8. The ID referenced in the response's "idnt" attribute specifies the folder where the sent message is saved.
	 *
	 * @param  array $ace Details of Draft to save.
	 * @return mix
	 */
	public function saveDraft(array $message)
	{
		return $this->_client->saveDraft(array('m' => $message));
	}

	/**
	 * Search.
	 * For a response, the order of the returned results represents the sorted order.
	 * There is not a separate index attribute or element.
	 *
	 * @param  string $query   Query string.
	 * @param  string $locale  Client locale identification.
	 * @param  array  $headers Headers.
	 * @param  array  $tz      Timezone specification.
	 * @param  array  $cursor  Cursor specification.
	 * @param  array  $attrs   Attributes.
	 * @return mix
	 */
	public function search($query = '', $locale = '', array $headers = array(), array $tz = array(), array $cursor = array(), array $attrs = array())
	{
		$params = array();
		if(!empty($query)) $params['query'] = $query;
		if(!empty($locale)) $params['locale'] = $locale;
		if(count($headers)) $params['header'] = $headers;
		if(count($tz)) $params['tz'] = $tz;
		if(count($cursor)) $params['cursor'] = $cursor;
		return $this->_client->search($params, $attrs);
	}

	/**
	 * Search a conversation.
	 *
	 * @param  string $query   Query string.
	 * @param  string $locale  Client locale identification.
	 * @param  array  $headers Headers.
	 * @param  array  $tz      Timezone specification.
	 * @param  array  $cursor  Cursor specification.
	 * @param  array  $attrs   Attributes.
	 * @return mix
	 */
	public function searchConv($query = '', $locale = '', array $headers = array(), array $tz = array(), array $cursor = array(), array $attrs = array())
	{
		$params = array();
		if(!empty($query)) $params['query'] = $query;
		if(!empty($locale)) $params['locale'] = $locale;
		if(count($headers)) $params['header'] = $headers;
		if(count($tz)) $params['tz'] = $tz;
		if(count($cursor)) $params['cursor'] = $cursor;
		return $this->_client->searchConv($params, $attrs);
	}

	/**
	 * Send a delivery report.
	 *
	 * @param  string $mid Message ID.
	 * @return mix
	 */
	public function sendDeliveryReport($mid)
	{
		return $this->_client->sendDeliveryReport(array(), array('mid' => $mid));
	}

	/**
	 * Send a reply to an invite.
	 *
	 * @param  string $id       Unique ID of the invite (and component therein) you are replying to.
	 * @param  string $compNum  Component number of the invite.
	 * @param  string $verb     Verb - ACCEPT, DECLINE, TENTATIVE, COMPLETED, DELEGATED (Completed/Delegated are NOT supported as of 9/12/2005).
	 * @param  string $update   Update organizer. Set by default. if unset then only make the update locally. This parameter has no effect if an <m> element is present.
	 * @param  string $idnt     Identity ID to use to send reply.
	 * @param  array  $message  Embedded message, if the user wants to send a custom update message. The client is responsible for setting the message recipient list in this case (which should include Organizer, if the client wants to tell the organizer about this response).
	 * @param  array  $exceptId If supplied then reply to just one instance of the specified Invite (default is all instances).
	 * @param  array  $tz       Definition for TZID referenced by DATETIME in <exceptId>.
	 * @return mix
	 */
	public function sendInviteReply($id, $compNum, $verb, $update = FALSE, $idnt = '', array $message = array(), array $exceptId = array(), array $tz = array())
	{
		$params = array();
		if(count($message)) $params['m'] = $message;
		if(count($exceptId)) $params['exceptId'] = $exceptId;
		if(count($tz)) $params['tz'] = $tz;

		$options = array(
			'id' => $id,
			'compNum' => $compNum,
			'verb' => $verb,
		);
		if((bool) $update) $options['updateOrganizer'] = 1;
		if(!empty($idnt)) $options['idnt'] = $idnt;
		return $this->_client->sendInviteReply($params, $options);
	}

	/**
	 * Send message.
	 *   1. Supports (f)rom, (t)o, (c)c, (b)cc, (r)eply-to, (s)ender, read-receipt (n)otification "type" on <e> elements.
	 *   2. Only allowed one top-level <mp> but can nest <mp>s within if multipart/*.
	 *   3. A leaf <mp> can have inlined content (<mp ct="{content-type}"><content>...</content></mp>).
	 *   4. A leaf <mp> can have referenced content (<mp><attach ...></mp>).
	 *   5. Any <mp> can have a Content-ID header attached to it.
	 *   6. On reply/forward, set origid on <m> element and set rt to "r" or "w", respectively.
	 *   7. Can optionally set identity-id to specify the identity being used to compose the message.
	 *   8. If noSave is set, a copy will not be saved to sent regardless of account/identity settings.
	 *   9. Can set priority high (!) or low (?) on sent message by specifying "f" attr on <m>
	 *   10. The message to be sent can be fully specified under the <m> element or, to compose the message remotely remotely, upload it via FileUploadServlet, and submit it through our server.
	 *   11. If the message is saved to the sent folder then the ID of the message is returned. Otherwise, no ID is returned -- just a <m> is returned.
	 *
	 * @param  string $message Specification of the message.
	 * @param  string $attrs   Attributes.
	 * @return mix
	 */
	public function sendMsg(array $message = array(), array $attrs = array())
	{
		$params = array();
		if(count($message)) $params['m'] = $message;
		return $this->_client->sendMsg($params, $attrs);
	}

	/**
	 * Send share notification.
	 * The client can list the recipient email addresses for the share, along with the itemId of the item being shared.
	 *
	 * @param  string $action Set to "revoke" if it is a grant revoke notification. It is set to "expire" by the system to send notification for a grant expiry.
	 * @param  string $item   Item ID.
	 * @param  string $emails Email addresses.
	 * @param  string $notes  Notes.
	 * @return mix
	 */
	public function sendShareNotification($action = '', $item = '', array $emails = array(), $notes = '')
	{
		$params = array();
		if(!empty($item)) $params['item'] = array('id' => $item);
		if(!empty($notes)) $params['notes'] = $notes;
		if(count($emails)) $params['e'] = $emails;
		$options = array();
		if(!empty($action)) $options['action'] = $action;
		return $this->_client->sendShareNotification($params, $options);
	}

	/**
	 * SendVerificationCodeRequest results in a random verification code being generated and sent to a device.
	 *
	 * @param  string $a Device email address.
	 * @return mix
	 */
	public function sendVerificationCode($a = '')
	{
		$options = array();
		if(!empty($a)) $options = array('a' => $a);
		return $this->_client->sendVerificationCode(array(), $options);
	}

	/**
	 * Directly set status of an entire appointment.
	 * This API is intended for mailbox Migration (ie migrating a mailbox onto this server) and is not used by normal mail clients.
	 * Need to specify folder for appointment 
	 * Need way to add message WITHOUT processing it for calendar parts.
	 * Need to generate and patch-in the iCalendar for the <inv> but w/o actually processing the <inv> as a new request.
	 *
	 * @param  array $default Default calendar item information.
	 * @param  array $except  Calendar item information for exceptions.
	 * @param  array $cancel  Calendar item information for cancellations.
	 * @param  array $replies Specification of the replies.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	public function setAppointment(array $default = array(), array $except = array(), array $cancel = array(), array $replies = array(), array $attrs = array())
	{
		$params = array();
		if(count($default)) $params['default'] = $default;
		if(count($except)) $params['except'] = $except;
		if(count($cancel)) $params['cancel'] = $cancel;
		if(count($replies)) $params['replies'] = $replies;
		return $this->_client->setAppointment($params, $attrs);
	}

	/**
	 * Set Custom Metadata.
	 * Setting a custom metadata section but providing no key/value pairs will remove the sction from the item.
	 *
	 * @param  string $id      Item ID.
	 * @param  string $section Section. Normally present. If absent this indicates that CustomMetadata info is present but there are no sections to report on.
	 * @param  array  $attrs   Attributes.
	 * @return mix
	 */
	public function setCustomMetadata($id, $section = '', array $attrs = array())
	{
		$params = array();
		$meta = array();
		if(!empty($section)) $meta['section'] = $section;
		$attributes = $this->_attributes($attrs);
		if(count($attributes)) $meta['a'] = $attributes;
		if(count($meta)) $params['meta'] = $meta;
		return $this->_client->setCustomMetadata($params, array('id' => $id));
	}

	/**
	 * Set Mailbox Metadata.
	 *   1. Setting a mailbox metadata section but providing no key/value pairs will remove the section from mailbox metadata.
	 *   2. Empty value not allowed
	 *   3. {metadata-section-key} must be no more than 36 characters long and must be in the format of {namespace}:{section-name}. currently the only valid namespace is "zwc".
	 *
	 * @param  string $section Section. Normally present. If absent this indicates that CustomMetadata info is present but there are no sections to report on.
	 * @param  array  $attrs   Attributes.
	 * @return mix
	 */
	public function setMailboxMetadata($section = '', array $attrs = array())
	{
		$params = array();
		$meta = array();
		if(!empty($section)) $meta['section'] = $section;
		$attributes = $this->_attributes($attrs);
		if(count($attributes)) $meta['a'] = $attributes;
		if(count($meta)) $params['meta'] = $meta;
		return $this->_client->setMailboxMetadata($params);
	}

	/**
	 * Directly set status of an entire task.
	 * See SetAppointment for more information.
	 *
	 * @param  array $default Default calendar item information.
	 * @param  array $except  Calendar item information for exceptions.
	 * @param  array $cancel  Calendar item information for cancellations.
	 * @param  array $replies Specification of the replies.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	public function setTask(array $default = array(), array $except = array(), array $cancel = array(), array $replies = array(), array $attrs = array())
	{
		$params = array();
		if(count($default)) $params['default'] = $default;
		if(count($except)) $params['except'] = $except;
		if(count($cancel)) $params['cancel'] = $cancel;
		if(count($replies)) $params['replies'] = $replies;
		return $this->_client->setTask($params, $attrs);
	}

	/**
	 * Snooze alarm(s) for appointments or tasks.
	 *
	 * @param  array $appt Snooze appointment alarm.
	 * @param  array $task Snooze task alarm.
	 * @return mix
	 */
	public function snoozeCalendarItemAlarm(array $appt = array(), array $task = array())
	{
		$params = array();
		if(count($appt)) $params['appt'] = $appt;
		if(count($task)) $params['task'] = $task;
		return $this->_client->snoozeCalendarItemAlarm($params);
	}

	/**
	 * Snooze alarm(s) for appointments or tasks.
	 *
	 * @param  string $token  Token - not provided for initial sync.
	 * @param  int    $cutoff Earliest Calendar date. If present, omit all appointments and tasks that don't have a recurrence ending after that time (specified in ms).
	 * @param  string $folder Root folder ID. If present, we start sync there rather than at folder 11.
	 * @param  bool   $typed  If specified and set, deletes are also broken down by item type.
	 * @return mix
	 */
	public function sync($token = '', $cutoff = 0, $folder = '', $typed = FALSE)
	{
		$options = array();
		if(!empty($token)) $options['token'] = $token;
		if(!empty($folder)) $options['l'] = $folder;
		if((int) $cutoff) $options['calCutoff'] = (int) $cutoff;
		if((bool) $typed) $options['typed'] = 1;
		return $this->_client->sync(array(), $options);
	}

	/**
	 * Perform an action on a tag.
	 *
	 * @param  array $action Specify action to perform.
	 * @return mix
	 */
	public function tagAction(array $action)
	{
		$params['action'] = $action;
		return $this->_client->tagAction($params);
	}

	/**
	 * Tests the connection to the specified data source.
	 * Does not modify the data source or import data.
	 * If the id is specified, uses an existing data source.
	 * Any values specified in the request are used in the test instead of the saved values.
	 *
	 * @param  string $type      Data source type. Valid values: imap|pop3|caldav|yab|rss|gal|cal|unknown.
	 * @param  array  $source    Data source specification.
	 * @param  array  $attrs     Data source attributes.
	 * @param  string $lastError Last error.
	 * @return mix
	 */
	public function testDataSource($type, array $source, array $attrs = array(), $lastError = '')
	{
		$params[$type] = $source;
		if(count($attrs))
		{
			$params[$type]['a'] = $attrs;
		}
		if(!empty($lastError))
		{
			$params[$type]['lastError'] = array('_' => $lastError);
		}
		return $this->_client->testDataSource($params);
	}

	/**
	 * Update device status.
	 *
	 * @param  string $id     Device ID.
	 * @param  string $status Status. Valid values: enabled|disabled|locked|wiped.
	 * @return mix
	 */
	public function updateDeviceStatus($id = '', $status = '')
	{
		$params['device'] = array();
		if(!empty($id)) $params['device']['id'] = $id;
		if(!empty($status)) $params['device']['status'] = $status;
		return $this->_client->updateDeviceStatus($params);
	}

	/**
	 * Validate the verification code sent to a device.
	 * After successful validation the server sets the device email address as the value of zimbraCalendarReminderDeviceEmail account attribute.
	 *
	 * @param  string $email Device email address.
	 * @param  string $code  Verification code.
	 * @return mix
	 */
	public function verifyCode($email = '', $code = '')
	{
		$options = array();
		if(!empty($email)) $params['email'] = $email;
		if(!empty($code)) $params['code'] = $code;
		return $this->_client->verifyCode(array(), $options);
	}

	/**
	 * WaitSetRequest optionally modifies the wait set and checks for any notifications.
	 * If block is set and there are no notificatins, then this API will BLOCK until there is data.
	 * Client should always set 'seq' to be the highest known value it has received from the server.
	 * The server will use this information to retransmit lost data.
	 * If the client sends a last known sync token then the notification is calculated by comparing the accounts current token with the client's last known.
	 * If the client does not send a last known sync token, then notification is based on change since last Wait (or change since <add> if this is the first time Wait has been called with the account)
	 * The client may specifiy a custom timeout-length for their request if they know something about the particular underlying network.
	 * The server may or may not honor this request (depending on server configured max/min values).
	 *
	 * @param  string $waitSet Waitset ID.
	 * @param  string $seq     Last known sequence number.
	 * @param  bool   $block   Flag whether or not to block until some account has new data.
	 * @param  int    $timeout Timeout length.
	 * @param  array  $types   Default interest types.
	 * @param  array  $add     WaitSet add specification.
	 * @param  array  $update  WaitSet update specification.
	 * @param  array  $remove  WaitSet remove specification
	 * @return mix
	 */
	public function waitSet($waitSet, $seq, $block = FALSE, $timeout = 0, array $types = array('all'), array $add = array(), array $update = array(), array $remove = array())
	{
		$options = array(
			'waitSet' => $waitSet,
			'seq' => $seq
		);
		if((bool) $block) $options['block'] = 1;
		if((int) $timeout) $options['timeout'] = (int) $timeout;
		$defTypes = $this->_commaAttributes($types);
		if(!empty($defTypes)) $options['defTypes'] = $defTypes;

		$params = array();
		if(count($add)) $params['add'] = $add;
		if(count($update)) $params['update'] = $update;
		if(count($remove)) $params['remove'] = $remove;
		return $this->_client->waitSet($params, $options);
	}
}
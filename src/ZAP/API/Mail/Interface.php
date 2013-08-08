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
 * ZAP_API_Mail_Interface is a interface which allows to connect Zimbra API mail functions via SOAP
 * @package   ZAP
 * @category  Mail
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
interface ZAP_API_Mail_Interface
{

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
	function addAppointmentInvite(array $message = array(), $ptst = '');

	/**
	 * Add a comment to the specified item. Currently comments can only be added to documents.
	 *
	 * @param  string $parentId Item ID of parent.
	 * @param  string $text     Comment text.
	 * @return mix
	 */
	function addComment($parentId, $text);

	/**
	 * Add a message.
	 *
	 * @param  array  $message Specification of the message to add.
	 * @param  string $content The entire message's content. (Omit if you specify an "aid" attribute.).
	 * @param  bool   $sent    If set, then do outgoing message filtering if the msg is being added to the Sent folder and has been flagged as sent. Default is unset.
	 * @return mix
	 */
	function addMsg(array $message, $content = '', $sent = FALSE);

	/**
	 * Add a task invite.
	 *
	 * @param  array  $message Specification of the message to add.
	 * @param  string $ptst    iCalendar PTST (Participation status). Valid values: NE|AC|TE|DE|DG|CO|IN|WE|DF. Meanings: "NE"eds-action, "TE"ntative, "AC"cept, "DE"clined, "DG" (delegated), "CO"mpleted (todo), "IN"-process (todo), "WA"iting (custom value only for todo), "DF" (deferred; custom value only for todo)
	 * @return mix
	 */
	function addTaskInvite(array $message = array(), $ptst = '');

	/**
	 * Announce change of organizer.
	 *
	 * @param  string $id ID.
	 * @return mix
	 */
	function announceOrganizerChange($id);

	/**
	 * Applies one or more filter rules to messages specified by a comma-separated ID list,
	 * or returned by a search query. One or the other can be specified, but not both.
	 * Returns the list of ids of existing messages that were affected.
	 * Note that redirect actions are ignored when applying filter rules to existing messages.
	 *
	 * @param  array $rules  Filter rules.
	 * @param  array $ids    Array of message IDs.
	 * @param  string $query Query string.
	 * @return mix
	 */
	function applyFilterRules(array $rules, array $ids = array(), $query = '');

	/**
	 * Applies one or more filter rules to messages specified by a comma-separated ID list,
	 * or returned by a search query. One or the other can be specified, but not both.
	 * Returns the list of ids of existing messages that were affected.
	 *
	 * @param  array $rules  Filter rules.
	 * @param  array $ids    Array of message IDs.
	 * @param  string $query Query string.
	 * @return mix
	 */
	function applyOutgoingFilterRules(array $rules, array $ids = array(), $query = '');

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
	function autoComplete($name, $type = 'account', array $folders = array(), $incGal = TRUE, $needExp = FALSE);

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
	function bounceMsg($id, array $emails = array());

	/**
	 * Browse.
	 *
	 * @param  string  $by    Browse by setting - domains|attachments|objects.
	 * @param  array   $regex Regex string. Return only those results which match the specified regular expression.
	 * @param  integer $max   Return only a maximum number of entries as requested. If more than {max-entries} results exist, the server will return the first {max-entries}, sorted by frequency.
	 * @return mix
	 */
	function browse($by, $regex = '', $max = 0);

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
	function cancelAppointment(array $inst = array(), array $tz = array(), array $message = array(), array $attrs = array());

	/**
	 * Cancel task.
	 *
	 * @param  array $inst    Instance recurrence ID information.
	 * @param  array $tz      Definition for TZID referenced by DATETIME in <inst>.
	 * @param  array $message Specification of the message.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	function cancelTask(array $inst = array(), array $tz = array(), array $message = array(), array $attrs = array());

	/**
	 * Check device status.
	 *
	 * @param  string $id Device ID.
	 * @return mix
	 */
	function checkDeviceStatus($id);

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
	function checkPermission($target, $type, array $rights = array());

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
	function checkRecurConflicts(array $tz, array $cancel, array $comp, array $except, array $usr, array $attrs = array());

	/**
	 * Check spelling.
	 * Suggested words are listed in decreasing order of their match score.
	 * The "available" attribute specifies whether the server-side spell checking interface is available or not.
	 *
	 * @param  string $dictionary The optional name of the aspell dictionary that will be used to check spelling. If not specified, the the dictionary will be either zimbraPrefSpellDictionary or the one for the account's locale, in that order.
	 * @param  array  $ignores     Array of words to ignore just for this request. These words are added to the user's personal dictionary of ignore words stored as zimbraPrefSpellIgnoreWord.
	 * @return mix
	 */
	function checkSpelling($dictionary = '',array $ignores = array());

	/**
	 * Complete a task instance.
	 *
	 * @param  string $id     ID.
	 * @param  array  $except Exception information.
	 * @param  array  $tz     Timezone information.
	 * @return mix
	 */
	function completeTaskInstance($id, array $except, array $tz = array());

	/**
	 * Contact Action.
	 *
	 * @param  array  $action Action selector.
	 * @return mix
	 */
	function contactAction(array $action);

	/**
	 * Conv Action.
	 *
	 * @param  array  $action Contact action selector.
	 * @param  array  $attrs  Attributes.
	 * @return mix
	 */
	function convAction();

	/**
	 * Propose a new time/location. Sent by meeting attendee to organizer.
	 * The syntax is very similar to CreateAppointmentRequest. 
	 *
	 * @param  array $details Details of counter proposal.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	function counterAppointment(array $details = array(), array $attrs = array());

	/**
	 * This is the API to create a new Appointment, optionally sending out meeting Invitations to other people.
	 *
	 * @param  array $message Specification of the message.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	function createAppointment(array $message = array(), array $attrs = array());

	/**
	 * Create Appointment Exception.
	 *
	 * @param  array $message Specification of the message.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	function createAppointmentException(array $message = array(), array $attrs = array());

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
	function createContact(array $cn, array $vcard = array(), array $members = array(), array $attrs = array() , $verbose = FALSE);

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
	function createDataSource($type, array $source, array $attrs = array(), $lastError = '');

	/**
	 * Create folder.
	 *
	 * @param  array $folder New folder specification.
	 * @param  array $grants Action grant selector.
	 * @return mix
	 */
	function createFolder(array $folder, array $grants = array());

	/**
	 * Create mountpoint.
	 *
	 * @param  array $link New mountpoint specification.
	 * @return mix
	 */
	function createMountpoint(array $link);

	/**
	 * Create a note.
	 *
	 * @param  array $note New note specification.
	 * @return mix
	 */
	function createNote(array $note);

	/**
	 * Create a search folder.
	 *
	 * @param  array $search New search folder specification.
	 * @return mix
	 */
	function createSearchFolder(array $search);

	/**
	 * Create a search folder.
	 *
	 * @param  array $tag Tag specification.
	 * @return mix
	 */
	function createTag(array $tag = array());

	/**
	 * This is the API to create a new Task.
	 *
	 * @param  array $message Specification of the message.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	function createTask(array $message, array $attrs = array());

	/**
	 * Create Task Exception.
	 *
	 * @param  array $message Specification of the message.
	 * @param  array $attrs   Attributes.
	 * @return mix
	 */
	function createTaskException(array $message, array $attrs = array());

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
	function createWaitSet(array $add, array $types = array(), $all = TRUE);

	/**
	 * Decline a change proposal from an attendee.
	 * Sent by organizer to an attendee who has previously sent a COUNTER message.
	 * The syntax of the request is very similar to CreateAppointmentRequest.
	 *
	 * @param  array $message Details of the Decline Counter. Should have an <inv> which encodes an iCalendar DECLINECOUNTER object.
	 * @return mix
	 */
	function declineCounterAppointment(array $message);

	/**
	 * Deletes the given data sources.
	 * The name or id of each data source must be specified.
	 *
	 * @param  string $datasources Array of data source.
	 * @return mix
	 */
	function deleteDataSource(array $datasources = array());

	/**
	 * Permanently deletes mapping for indicated device.
	 *
	 * @param  string $id Device ID.
	 * @return mix
	 */
	function deleteDevice($id);

	/**
	 * Use this to close out the waitset.
	 * Note that the server will automatically time out a wait set if there is no reference to it for (default of) 20 minutes.
	 * WaitSet: scalable mechanism for listening for changes to one or more accounts.
	 *
	 * @param  string $id Waitset ID.
	 * @return mix
	 */
	function destroyWaitSet($id)

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
	function diffDocument($id = '', $v1 = 0, $v2 = 0);

	/**
	 * Dismiss calendar item alarm.
	 *
	 * @param  array $appt Dismiss appointment alarm.
	 * @param  array $task Dismiss task alarm.
	 * @return mix
	 */
	function dismissCalendarItemAlarm(array $appt = array(), array $task = array());

	/**
	 * Document action.
	 *
	 * @param  array $action Document action selector. Document specific operations : watch|!watch|grant|!grant.
	 * @param  array $grant  Document action grant.
	 * @return mix
	 */
	function documentAction(array $action, array $grant = array());

	/**
	 * Empty dumpster.
	 *
	 * @return mix
	 */
	function emptyDumpster();

	/**
	 * Enable/disable reminders for shared appointments/tasks on a mountpoint.
	 *
	 * @param  array $id       Mountpoint ID.
	 * @param  bool  $reminder Set to enable (or unset to disable) reminders for shared appointments/tasks.
	 * @return mix
	 */
	function enableSharedReminder($id, $reminder = TRUE);

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
	function expandRecur(array $tz, array $comp = array(), array $except = array(), array $cancel = array(), array $attrs = array());

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
	function exportContacts($ct, $folder = '', $csvfmt = '', $csvlocale = '', $csvsep = '');

	/**
	 * Perform an action on a folder.
	 *
	 * @param  array $action    Select action to perform on folder.
	 * @param  array $grant     Action grant selector.
	 * @param  array $aclGrant  Action acl grant selector.
	 * @param  array $retention Retention policy.
	 * @return mix
	 */
	function folderAction(array $action, array $grant = array(), array $aclGrant = array(), array $retention = array());

	/**
	 * Used by an attendee to forward an instance or entire appointment to another user who is not already an attendee.
	 *
	 * @param  string $id       Appointment item ID.
	 * @param  array  $exceptId RECURRENCE-ID information if forwarding a single instance of a recurring appointment.
	 * @param  array  $tz       Definition for TZID referenced by DATETIME in <exceptId>.
	 * @param  array  $message  Details of the appointment.
	 * @return mix
	 */
	function forwardAppointment($id = '', array $exceptId, array $tz = array(), array $message = array());

	/**
	 * Used by an attendee to forward an appointment invite email to another user who is not already an attendee.
	 * To forward an appointment item, use ForwardAppointmentRequest instead.
	 *
	 * @param  array  $message  Details of the invite.
	 * @return mix
	 */
	function forwardAppointmentInvite(array $message = array());

	/**
	 * Ajax client can use this request to ask the server for help in generating a proper,
	 * globally unique UUID.
	 *
	 * @return mix
	 */
	function generateUUID();

	/**
	 * Get activity stream.
	 *
	 * @param  string  $id Item ID. If the id is for a Document, the response will include the activities for the requested Document. if it is for a Folder, the response will include the activities for all the Documents in the folder and subfolders.
	 * @param  array   $filter  Optionally <filter> can be used to filter the response based on the user that performed the activity, operation, or both. the server will cache previously established filter search results, and return the identifier in session attribute. The client is expected to reuse the session identifier in the subsequent filter search to improve the performance.
	 * @param  integer $limit   Limit - maximum number of activities to be returned
	 * @param  integer $offset  Offset - for getting the next page worth of activities.
	 * @return mix
	 */
	function getActivityStream($id, array $filter = array(), $limit = 0, $offset = 0);

	/**
	 * Get all devices.
	 *
	 * @return mix
	 */
	function getAllDevices();

	/**
	 * Get appointment.
	 * Returns the metadata info for each Invite that makes up this appointment.
	 *
	 * @param  string $id Appointment ID. Either id or uid should be specified, but not both.
	 * @param  string $uid iCalendar UID Either id or uid should be specified, but not both.
	 * @param  bool   $sync Set this to return the modified date (md) on the appointment.
	 * @param  bool   $includeContent If true, MIME parts for body content are returned; default false.
	 * @return mix
	 */
	function getAppointment($id = '', $uid = '', $sync = TRUE, $includeContent = FALSE);

	/**
	 * Get appointment summaries.
	 *
	 * @param  integer $start  Range start in milliseconds since the epoch GMT.
	 * @param  integer $end    Range end in milliseconds since the epoch GMT.
	 * @param  string  $folder Folder Id. Optional folder to constrain requests to; otherwise, searches all folders but trash and spam.
	 * @return mix
	 */
	function getApptSummaries($start, $end, $folder = '');

	/**
	 * Get Calendar item summaries.
	 *
	 * @param  integer $start  Range start in milliseconds since the epoch GMT.
	 * @param  integer $end    Range end in milliseconds since the epoch GMT.
	 * @param  string  $folder Folder Id.
	 * @return mix
	 */
	function getCalendarItemSummaries($start, $end, $folder = '');

	/**
	 * Get comments.
	 *
	 * @param  string $parentId Item ID of parent.
	 * @return mix
	 */
	function getComments($parentId);
}

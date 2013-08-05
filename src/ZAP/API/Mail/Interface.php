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
 * ZAP_API_Mail_Interface is a interface which allows to connect Zimbra API mail functions via SOAP
 * @package   ZAP
 * @category  Mail
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
interface ZAP_API_Mail_Interface
{

	/**
	 * Get Appointment. Returns the metadata info for each Invite that makes up this appointment. 
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

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
 * ZAP_API_Admin_Interface is a interface which allows to connect Zimbra API administration functions via SOAP
 * @package   ZAP
 * @category  Admin
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
interface ZAP_API_Admin_Interface
{
	/**
	 * Aborts a running HSM process.
	 * Network edition only API.
	 *
	 * @return mix
	 */
	function abortHsm();

	/**
	 * Aborts a running HSM process.
	 * Network edition only API.
	 *
	 * @param  integer $searchID Search task identify.
	 * @param  string  $account  Select account.
	 * @return mix
	 */
	function abortXMbxSearch($searchID, $account);

	/**
	 * Activate License.
	 * Network edition only API.
	 *
	 * @param  string $aid Attachment identify.
	 * @return mix
	 */
	function activateLicense($aid);

	/**
	 * Add an alias for the account.
	 * Access: domain admin sufficient.
	 * Note: this request is by default proxied to the account's home server.
	 *
	 * @param  string $id    Value of zimbra identify.
	 * @param  string $alias Account alias.
	 * @return mix
	 */
	function addAccountAlias($id, $alias);

	/**
	 * Changes logging settings on a per-account basis.
	 * Adds a custom logger for the given account and log category.
	 * The logger stays in effect only during the lifetime of the current server instance.
	 * If the request is sent to a server other than the one that the account resides on,
	 * it is proxied to the correct server.
	 * If the category is "all", adds a custom logger for every category or the given user.
	 *
	 * @param  string $account  The user account.
	 * @param  string $category Logger category.
	 * @param  string $level    Level of the logging (error|warn|info|debug|trace).
	 * @return mix
	 */
	function addAccountLogger($account, $category, $level = 'error');

	/**
	 * Add an alias for a distribution list.
	 * Access: domain admin sufficient.
	 *
	 * @param  string $id   Value of zimbra identify.
	 * @param  string $alias Distribution list alias.
	 * @return mix
	 */
	function addDistributionListAlias($id, $alias);

	/**
	 * Adding members to a distribution list.
	 * Access: domain admin sufficient.
	 *
	 * @param  string $id      Value of zimbra identify.
	 * @param  array  $members Distribution list members.
	 * @return mix
	 */
	function addDistributionListMember($id, array $members);

	/**
	 * Add a GalSync data source.
	 * Access: domain admin sufficient.
	 *
	 * @param  string $name    Name of the data source.
	 * @param  string $domain  Name of pre-existing domain.
	 * @param  string $type    GalMode type (both|ldap|zimbra).
	 * @param  string $account Account.
	 * @param  string $folder  Contact folder name.
	 * @param  array  $attrs   Attributes.
	 * @return mix
	 */
	function addGalSyncDataSource($name, $domain, $type, $account, $folder = '', array $attrs = array());

	/**
	 * Create a waitset to listen for changes on one or more accounts.
	 * Called once to initialize a WaitSet and to set its "default interest types".
	 * WaitSet: scalable mechanism for listening for changes to one or more accounts.
	 * Interest types:
	 *   f. folders
	 *   m. messages
	 *   c. contacts
	 *   a. appointments
	 *   t. tasks
	 *   d. documents
	 *   all. all types (equiv to "f,m,c,a,t,d")
	 *
	 * @param  array  $add   Waitset.
	 * @param  string $types Default interest types.
	 * @param  bool   $all   If all is set, then all mailboxes on the system will be listened to, including any mailboxes which are created on the system while the WaitSet is in existence.
	 * @return mix
	 */
	function adminCreateWaitSet(array $add, array $types = array(), $all = FALSE);

	/**
	 * Use this to close out the waitset.
	 * Note that the server will automatically time out a wait set
	 * if there is no reference to it for (default of) 20 minutes.
	 * WaitSet: scalable mechanism for listening for changes to one or more accounts.
	 *
	 * @param  string $waitSet Waitset identify.
	 * @return mix
	 */
	function adminDestroyWaitSet($waitSet);

	/**
	 * AdminWaitSetRequest optionally modifies the wait set and checks for any notifications.
	 * If block=1 and there are no notifications, then this API will BLOCK until there is data.
	 * Interest types:
	 *   f. folders
	 *   m. messages
	 *   c. contacts
	 *   a. appointments
	 *   t. tasks
	 *   d. documents
	 *   all. all types (equiv to "f,m,c,a,t,d")
	 *
	 * @param  string  $waitSet Waitset identify.
	 * @param  string  $seq     Last known sequence number.
	 * @param  array   $add     Waitset to add.
	 * @param  array   $update  Waitset to update.
	 * @param  array   $remove  Waitset to remove.
	 * @param  array   $types   Default interest types.
	 * @param  bool    $block   Flag whether or not to block until some account has new data.
	 * @param  integer $timeout Timeout length.
	 * @return mix
	 */
	function adminWaitSet($waitSet, $seq, array $add = array(), array $update = array(), array $remove = array(), array $types = array(), $block = 0, $timeout = 0);

	/**
	 * Authenticate for an adminstration account.
	 *
	 * @param  string $account  The user account.
	 * @param  string $password The user password.
	 * @param  string $vhost    Virtual-host is used to determine the domain of the account name.
	 * @return authentication token
	 */
	function auth($account, $password, $vhost = '');

	/**
	 * Authenticate for an adminstration account.
	 *
	 * @param  string $account The adminstration account.
	 * @param  string $token   The authentication token.
	 * @param  string $vhost   Virtual-host is used to determine the domain of the account name.
	 * @return authentication token.
	 */
	function authByToken($account, $token, $vhost = '');

	/**
	 * Perform an autocomplete for a name against the Global Address List
	 * Notes: admin verison of mail equiv. Used for testing via zmprov.
	 * Type of addresses to auto-complete on:
	 *   1. "account" for regular user accounts, aliases and distribution lists
	 *   2. "resource" for calendar resources
	 *   3. "group" for groups
	 *   4. "all" for combination of types
	 *
	 * @param  string $domain The domain name.
	 * @param  string $name   The name to test for autocompletion.
	 * @param  string $type   Type of addresses to auto-complete on.
	 * @param  string $acctID GAL Account ID.
	 * @return mix
	 */
	function autoCompleteGal($domain, $name, $type = 'accounts', $acctID = '');

	/**
	 * Auto-provision an account
	 *
	 * @param  string $domain    The domain name.
	 * @param  string $principal The name used to identify the principal.
	 * @param  string $password  Password.
	 * @return mix
	 */
	function autoProvAccount($domain, $principal, $password = '');

	/**
	 * Auto-provision task control.
	 * Under normal situations, the EAGER auto provisioning task(thread)
	 * should be started/stopped automatically by the server when appropriate.
	 * The task should be running when zimbraAutoProvPollingInterval is not 0
	 * and zimbraAutoProvScheduledDomains is not empty.
	 * The task should be stopped otherwise.
	 * This API is to manually force start/stop or query status of the EAGER auto provisioning task.
	 * It is only for diagnosis purpose and should not be used under normal situations.
	 *
	 * @param  string $action Action to perform - one of start|status|stop
	 * @return mix
	 */
	function autoProvTaskControl($action);

	/**
	 * Do a backup <account> elements are required when method=full
	 * and server is running in standard backup mode.
	 * If server is running in auto-grouped backup mode,
	 * omit the account list in full backup request to trigger auto-grouped backup.
	 * If account list is specified, only those accounts will be backed up.
	 * Note: Network edition only API.
	 *
	 * @param  string $backup   Backup specification.
	 * @param  string $file     File copier specification.
	 * @param  string $accounts Account selector - either one <account name="all"/> or a list of <account name="{account email addr}"/>
	 * @return mix
	 */
	function backup(array $backup, array $file = array(), array $accounts = array('all'));

	/**
	 * Backup Account query.
	 * For each account <backup> is listed from the most recent to earlier ones.
	 * Network edition only API.
	 *
	 * @param  string $query    Query specification.
	 * @param  array  $accounts Either the account email address or all.
	 * @return mix
	 */
	function backupAccountQuery(array $query, array $accounts = array('all'));

	/**
	 * Backup Query.
	 * Network edition only API.
	 *
	 * @param  string $query Query specification.
	 * @return mix
	 */
	function backupQuery(array $query);

	/**
	 * Cancel a pending Remote Wipe request.
	 * Remote Wipe can't be canceled once the device confirms the wipe.
	 * Network edition only API.
	 *
	 * @param  string $account The name used to identify the account.
	 * @param  string $device  Device ID.
	 * @return mix
	 */
	function cancelPendingRemoteWipe($account, $device = '');

	/**
	 * Check Auth Config.
	 *
	 * @param  string $name     Name.
	 * @param  string $password Password.
	 * @param  array  $attrs    Attributes.
	 * @return mix
	 */
	function checkAuthConfig($name, $password, array $attrs = array());

	/**
	 * Checks for items that have no blob, blobs that have no item,
	 * and items that have an incorrect blob size stored in their metadata.
	 * If no volumes are specified, all volumes are checked.
	 * If no mailboxes are specified, all mailboxes are checked.
	 * Blob sizes are checked by default.
	 * Set checkSize to 0 (false) to * avoid the CPU overhead
	 * of uncompressing compressed blobs in order to calculate size.
	 *
	 * @param  string $checkSize Check size.
	 * @param  string $report    If set a complete list of all blobs used by the mailbox(es) is returned.
	 * @param  array  $volumes   Volumes.
	 * @param  array  $mboxes    Mailboxes.
	 * @return mix
	 */
	function checkBlobConsistency($checkSize = 0, $report = 0, array $volumes = array(), array $mboxes = array());

	/**
	 * Check existence of one or more directories and optionally create them.
	 *
	 * @param  array $directories Directories.
	 * @return mix
	 */
	function checkDirectory(array $directories = array());

	/**
	 * Check Domain MX record.
	 *
	 * @param  string $domain The name used to identify the domain.
	 * @return mix
	 */
	function checkDomainMXRecord($domain);

	/**
	 * Check Exchange Authorisation.
	 *
	 * @param  string $url URL to Exchange server.
	 * @param  string $user Exchange user.
	 * @param  string $pass Exchange password.
	 * @param  string $scheme Auth scheme (basic|form).
	 * @return mix
	 */
	function checkExchangeAuth($url, $user, $pass, $scheme = 'basic');

	/**
	 * Check Global Addressbook Configuration .
	 * Notes:
	 *   1. zimbraGalMode must be set to ldap, even if you eventually want to set it to "both".
	 *   2. <action> is optional. GAL-action can be autocomplete|search|sync. Default is search.
	 *   3. <query> is ignored if <action> is "sync".
	 *   4. AuthMech can be none|simple|kerberos5.
	 *      - Default is simple if both BindDn/BindPassword are provided.
	 *      - Default is none if either BindDn or BindPassword are NOT provided.
	 *   5. BindDn/BindPassword are required if AuthMech is "simple".
	 *   6. Kerberos5Principal/Kerberos5Keytab are required only if AuthMech is "kerberos5".
	 *   7. zimbraGalSyncLdapXXX attributes are for GAL sync. They are ignored if <action> is not sync. 
	 *      For GAL sync, if a zimbraGalSyncLdapXXX attribute is not set,
	 *      server will fallback to the corresponding zimbraGalLdapXXX attribute.
	 *
	 * @param  string  $query  Description for element text content.
	 * @param  string  $action Action (autocomplete|search|sync).
	 * @param  integer $limit  Limit. Default value 10
	 * @param  array   $attrs  Attributes.
	 * @return mix
	 */
	function checkGalConfig($query, $action = 'search', $limit = 10, array $attrs = array());

	/**
	 * Check Health.
	 *
	 * @return mix
	 */
	function checkHealth();

	/**
	 * Check whether a hostname can be resolved.
	 *
	 * @param  string $hostname Hostname.
	 * @return mix
	 */
	function checkHostnameResolve($hostname = '');

	/**
	 * Check password strength.
	 * Access: domain admin sufficient.
	 * Note: this request is by default proxied to the account's home server
	 *
	 * @param  string $id       Zimbra identify.
	 * @param  string $password Passowrd to check.
	 * @return mix
	 */
	function checkPasswordStrength($id, $password);

	/**
	 * Check if a principal has the specified right on target. 
	 * A successful return means the principal specified by the <grantee>
	 * is allowed for the specified right on the * target object. 
	 * Note: this request is by default proxied to the account's home server
	 *
	 * @param  string $right   Name of right.
	 * @param  string $target  The name used to identify the target.
	 * @param  string $type    Target type. Valid values: (account|calresource|cos|dl|group|domain|server|ucservice|xmppcomponent|zimlet|config|global).
	 * @param  array  $grantee Grantee.
	 * @param  array  $attrs   Attributes.
	 * @return mix
	 */
	function checkRights($right, $target, $type, array $grantee, array $attrs = array());

	/**
	 * Clear cookie.
	 *
	 * @param  array $cookies Specifies cookies to clean.
	 * @return mix
	 */
	function clearCookie(array $cookies = array());

	/**
	 * Compact index.
	 * Access: domain admin sufficient.
	 * Note: this request is by default proxied to the account's home server.
	 *
	 * @param  string $id     Account identify.
	 * @param  string $action Action to perform (start|status).
	 * @return mix
	 */
	function compactIndex($id, $action = 'status');

	/**
	 * Computes the aggregate quota usage for all domains in the system.
	 * The request handler issues GetAggregateQuotaUsageOnServerRequest
	 * to all mailbox servers and computes the aggregate quota used by each domain.
	 * The request handler updates the zimbraAggregateQuotaLastUsage domain attribute
	 * and sends out warning messages for each domain having quota usage greater than a defined percentage threshold.
	 *
	 * @return mix
	 */
	function computeAggregateQuotaUsage();

	/**
	 * Configure Zimlet.
	 *
	 * @param  string $aid Attachment identify.
	 * @return mix
	 */
	function configureZimlet($aid);

	/**
	 * Copy Class of service (COS).
	 *
	 * @param  string $name Destination name for COS.
	 * @param  string $cos  Source COS.
	 * @return mix
	 */
	function copyCos($name, $cos);

	/**
	 * Count number of accounts by cos in a domain.
	 * Note: It doesn't include any account with zimbraIsSystemResource=TRUE,
	 *       nor does it include any calendar resources.
	 *
	 * @param  string $domain The name used to identify the domain.
	 * @return mix
	 */
	function countAccount($domain = '');

	/**
	 * Count number of objects. 
	 * Returns number of objects of requested type.
	 * Note: For account/alias/dl, if a domain is specified,
	 *       only entries on the specified domain are counted.
	 *       If no domain is specified, entries on all domains are counted.
	 *       For accountOnUCService/cosOnUCService/domainOnUCService,
	 *       UCService is required, and domain cannot be specified.
	 *
	 * @param  string $domain    The name used to identify the domain.
	 * @param  string $type      Object type. Valid values: (userAccount|account|alias|dl|domain|cos|server|calresource|accountOnUCService|cosOnUCService|domainOnUCService|internalUserAccount|internalArchivingAccount).
	 * @param  string $ucservice Key for choosing ucservice.
	 * @return mix
	 */
	function countObjects($domain = '', $type = 'account', $ucservice = '');

	/**
	 * Create account.
	 * Notes:
	 *   1. accounts without passwords can't be logged into.
	 *   2. name must include domain (uid@name), and domain specified in name must exist.
	 *   3. default value for zimbraAccountStatus is "active".
	 * Access: domain admin sufficient.
	 *
	 * @param  string $name     New account's name. Must include domain (uid@name), and domain specified in name must exist.
	 * @param  string $password New account's password.
	 * @param  array  $attrs    Attributes.
	 * @return mix
	 */
	function createAccount($name, $password, array $attrs = array());

	/**
	 * Create an archive.  
	 * Notes:
	 *   1. If <name> if not specified, archive account name is computed based on name templates.
	 *   2. Recommended that password not be specified so only admins can login.
	 *   3. A newly created archive account is always defaulted with the following attributes.
	 *      You can override these attributes (or set additional ones) by specifying <a> elements in <archive>.
	 * Access: domain admin sufficient.
	 * Network edition only API.
	 *
	 * @param  string $account  The name used to identify the account.
	 * @param  string $name     Archive account name. If not specified, archive account name is computed based on name templates.
	 * @param  string $cos      Selector for Class Of Service (COS).
	 * @param  string $password Archive account password - Recommended that password not be specified so only admins can login.
	 * @param  array  $attrs    Attributes.
	 * @return mix
	 */
	function createArchive($account, $name = '', $cos = '', $password = '', array $attrs = array());

	/**
	 * Create a calendar resource.
	 * Notes:
	 *   1. A calendar resource is a special type of Account. The Create, Delete, Modify, Rename, Get, GetAll, and Search operations are very similar to those of Account.
	 *   2. Must specify the displayName and zimbraCalResType attributes
	 * Access: domain admin sufficient.
	 *
	 * @param  string $name     Name or calendar resource. Must include domain (uid@domain), and domain specified after @ must exist.
	 * @param  string $password Password for calendar resource.
	 * @param  array  $attrs    Attributes.
	 * @return mix
	 */
	function createCalendarResource($name, $password = '', array $attrs = array());

	/**
	 * Create a Class of Service (COS).
	 * Notes:
	 *   1. Extra attrs: description, zimbraNotes.
	 *
	 * @param  string $name  COS name.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function createCos($name, array $attrs = array());

	/**
	 * Creates a data source that imports mail items into the specified folder.
	 * Notes:
	 *   1. Currently the only type supported is pop3.
	 *   2. every attribute value is returned except password.
	 *   3. this request is by default proxied to the account's home server.
	 *
	 * @param  string $id    ID for an existing Account.
	 * @param  string $type  Data source type. Valid values: (pop3|imap|caldav|contacts|yab|rss|cal|gal|xsync|tagmap).
	 * @param  string $name  Data source name.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function createDataSource($id, $type, $name, array $attrs = array());

	/**
	 * Create a distribution list.
	 * Notes:
	 *   1. dynamic - create a dynamic distribution list.
	 *   2. Extra attrs: description, zimbraNotes.
	 *
	 * @param  string $name    Name for distribution list.
	 * @param  array  $attrs   Attributes.
	 * @param  bool   $dynamic If 1 (true) then create a dynamic distribution list.
	 * @return mix
	 */
	function createDistributionList($name, array $attrs = array(), $dynamic = FALSE);

	/**
	 * Create a domain.
	 * Note:
	 *   1. Extra attrs: description, zimbraNotes.
	 *
	 * @param  string $name  Name of new domain.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function createDomain($name, array $attrs = array());

	/**
	 * Create a domain.
	 * Notes:
	 *   1. if the referenced account is not found it will be created.
	 *   2. the identifier used in name attr is used for SyncGal and SearchGal.
	 *   3. name attribute is for the name of the data source.
	 *   4. if folder attr is not present it'll default to Contacts folder.
	 *   5. passed in attrs in <a/> are used to initialize the gal data source.
	 *   6. server is a required parameter and specifies the mailhost on which this account resides.
	 *
	 * @param  string $name     Name of the data source.
	 * @param  string $domain   Domain name.
	 * @param  string $server   The mailhost on which this account resides.
	 * @param  string $account  The name used to identify the account.
	 * @param  string $type     GalMode type. Valid values: (both|ldap|zimbra).
	 * @param  string $password Password.
	 * @param  string $folder   Contact folder name.
	 * @param  array  $attrs    Attributes.
	 * @return mix
	 */
	function createGalSyncAccount($name, $domain, $server, $account, $type = 'both', $password = '', $folder = '', array $attrs = array());

	/**
	 * Create an LDAP entry.
	 *
	 * @param  string $dn    A valid LDAP DN String (RFC 2253) that describes the new DN to create.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function createLDAPEntry($dn, array $attrs = array());

	/**
	 * Create a Server.
	 * Extra attrs: description, zimbraNotes.
	 *
	 * @param  string $name  New server name.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function createServer($name, array $attrs = array());

	/**
	 * Create a system retention policy.
	 * The system retention policy SOAP APIs allow the administrator
	 * to edit named system retention policies that users can apply to folders and tags.
	 *
	 * @param  string $cos   The name used to identify the COS.
	 * @param  array  $keep  Keep policy details.
	 * @param  array  $purge Purge policy details.
	 * @return mix
	 */
	function createSystemRetentionPolicy($cos = '', array $keep = array(), array $purge = array());

	/**
	 * Create a UC service.
	 *
	 * @param  string $name  New ucservice name.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function createUCService($name, array $attrs = array());

	/**
	 * Create a volume.
	 *
	 * @param  array $volume Volume information.
	 * @return mix
	 */
	function createVolume(array $volume);

	/**
	 * Create an XMPP component.
	 *
	 * @param  string $name   XMPP name.
	 * @param  string $domain Domain name selector.
	 * @param  string $server Server name selector.
	 * @param  array  $attrs  Attributes.
	 * @return mix
	 */
	function createXMPPComponent($name, $domain, $server, array $attrs = array());

	/**
	 * Creates a search task.
	 * Network edition only API.
	 *
	 * @param  array $attrs Attributes.
	 * @return mix
	 */
	function createXMbxSearch(array $attrs = array());

	/**
	 * Create a Zimlet.
	 *
	 * @param  string $name  Zimlet name.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function createZimlet($name, array $attrs = array());

	/**
	 * Dedupe the blobs having the same digest.
	 *
	 * @param  string $action  Action to perform - one of start|status|stop.
	 * @param  array  $volumes Volumes.
	 * @return mix
	 */
	function dedupeBlobs($action, array $volumes = array());

	/**
	 * Used to request a new auth token that is valid for the specified account.
	 * The id of the auth token will be the id of the target account,
	 * and the requesting admin's id will be stored in the auth token for auditing purposes.
	 *
	 * @param  string $account  The name used to identify the account.
	 * @param  long   $duration Lifetime in seconds of the newly-created authtoken. defaults to 1 hour. Can't be longer then zimbraAuthTokenLifetime.
	 * @return mix
	 */
	function delegateAuth($account, $duration = 3600);

	/**
	 * Deletes the account with the given id.
	 * Notes:
	 *   1. If the request is sent to the server on which the mailbox resides,
	 *      the mailbox is deleted as well.
	 *   1. this request is by default proxied to the account's home server.
	 *
	 * @param  string $id  Zimbra identify.
	 * @return mix
	 */
	function deleteAccount($id);

	/**
	 * Deletes the calendar resource with the given id.
	 * Note: this request is by default proxied to the account's home server .
	 * Access: domain admin sufficient.
	 *
	 * @param  string $id  Zimbra identify.
	 * @return mix
	 */
	function deleteCalendarResource($id);

	/**
	 * Delete a Class of Service (COS).
	 *
	 * @param  string $id  Zimbra identify.
	 * @return mix
	 */
	function deleteCos($id);

	/**
	 * Deletes the given data source.
	 * Note: this request is by default proxied to the account's home server.
	 *
	 * @param  string $id         ID for an existing Account.
	 * @param  string $dataSource Data source ID.
	 * @param  array  $attrs      Attributes.
	 * @return mix
	 */
	function deleteDataSource($id, $dataSource, array $attrs = array());

	/**
	 * Delete a distribution list.
	 * Access: domain admin sufficient.
	 *
	 * @param  string $id Zimbra ID for distribution list.
	 * @return mix
	 */
	function deleteDistributionList($id);

	/**
	 * Delete a domain.
	 *
	 * @param  string $id Zimbra ID for domain.
	 * @return mix
	 */
	function deleteDomain($id);

	/**
	 * Delete a Global Address List (GAL) Synchronisation account.
	 * Remove its zimbraGalAccountID from the domain, then deletes the account.
	 *
	 * @param  string $account The name used to identify the account.
	 * @return mix
	 */
	function deleteGalSyncAccount($account);

	/**
	 * Delete an LDAP entry.
	 *
	 * @param  string $dn A valid LDAP DN String (RFC 2253) that describes the DN to delete.
	 * @return mix
	 */
	function deleteLDAPEntry($dn);

	/**
	 * Delete a mailbox.
	 * The request includes the account ID (uuid) of the target mailbox on success,
	 * the response includes the mailbox ID (numeric) of the deleted mailbox
	 * the <mbox> element is left out of the response if no mailbox existed for that account.
	 * Note: this request is by default proxied to the account's home server 
	 * Access: domain admin sufficient
	 *
	 * @param  string $id Account ID.
	 * @return mix
	 */
	function deleteMailbox($id);

	/**
	 * Delete a server.
	 * Note: this request is by default proxied to the referenced server.
	 *
	 * @param  string $id Zimbra ID.
	 * @return mix
	 */
	function deleteServer($id);

	/**
	 * Delete a system retention policy.
	 *
	 * @param  array  $policy Retention policy.
	 * @param  string $cos    The name used to identify the COS.
	 * @return mix
	 */
	function deleteSystemRetentionPolicy(array $policy, $cos = '');

	/**
	 * Delete a UC service.
	 *
	 * @param  string $id Zimbra ID.
	 * @return mix
	 */
	function deleteUCService($id);

	/**
	 * Delete a UC service.
	 *
	 * @param  string $id Volume ID.
	 * @return mix
	 */
	function deleteVolume($id);

	/**
	 * Delete an XMPP Component.
	 *
	 * @param  string $xmpp The name used to identify the XMPP component.
	 * @return mix
	 */
	function deleteXMPPComponent($xmpp);

	/**
	 * Attempts to delete a search task.
	 * Returns empty <DeleteXMbxSearchResponse/> element on success or Fault document on error.
	 * Network edition only API.
	 *
	 * @param  string $searchId Search ID.
	 * @param  string $account  The name used to identify the account..
	 * @return mix
	 */
	function deleteXMbxSearch($searchId, $account = '');

	/**
	 * Delete a Zimlet.
	 *
	 * @param  string $name Zimlet name.
	 * @return mix
	 */
	function deleteZimlet($name);

	/**
	 * Delete a Zimlet.
	 *
	 * @param  string $action Action - valid values : deployAll|deployLocal|status.
	 * @param  string $aid    Attachment ID.
	 * @param  bool   $flush  Flag whether to flush the cache.
	 * @param  bool   $sync   Synchronous flag.
	 * @return mix
	 */
	function deployZimlet($action, $aid, $flush = FALSE, $sync = FALSE);

	/**
	 * Disable Archiving for an account that already has archiving enabled.
	 * Network edition only API.
	 *
	 * @param  string $account The name used to identify the account.
	 * @return mix
	 */
	function disableArchive($account);

	/**
	 * Dump sessions.
	 *
	 * @param  bool $list List Sessions flag.
	 * @param  bool $groupBy Group by account flag.
	 * @return mix
	 */
	function dumpSessions($list = TRUE, $groupBy = FALSE);

	/**
	 * Enable Archive.
	 *  1. Archive account is created by default based on name templates.
	 *     You can suppress this by sending create=0.
	 *     This is useful if you are going to use a third party system to do the archiving
	 *     and ZCS is just a mail forker.
	 *  2. Recommended that password not be specified so only admins can login.
	 *  3. A newly created archive account is always defaulted with the following attributes.
	 *     You can override these attributes (or set additional ones)
	 *     by specifying <a> elements in <archive>.
	 * Network edition only API.
	 *
	 * @param  string $account  The name used to identify the account.
	 * @param  string $name     Archive account name. If not specified, archive account name is computed based on name templates.
	 * @param  string $create   Archive account is created by default based on name templates. You can suppress this by setting this flag to 0 (false). This is useful if you are going to use a third party system to do the archiving and ZCS is just a mail forker.
	 * @param  string $cos      Selector for Class Of Service (COS).
	 * @param  string $password Archive account password - Recommended that password not be specified so only admins can login.
	 * @param  array  $attrs    Attributes.
	 * @return mix
	 */
	function enableArchive($account, $name, $create = FALSE, $cos = '', $password = '', array $attrs = array());

	/**
	 * Exports the database data for the given items with SELECT INTO OUTFILE
	 * and deletes the items from the mailbox.
	 * Exported filenames follow the pattern {prefix}{table_name}.txt.
	 * The files are written to sqlExportDir.
	 * When sqlExportDir is not specified, data is not exported.
	 * Export is only supported for MySQL.
	 *
	 * @param  string $id     Mailbox ID.
	 * @param  string $dir    Path for export dir.
	 * @param  string $prefix Export filename prefix.
	 * @param  array $items   Mailbox items.
	 * @return mix
	 */
	function exportAndDeleteItems($id, $dir, $prefix = '', array $items = array());

	/**
	 * Export Mailbox (OLD mailbox move mechanism).
	 * This request blocks until mailbox move is complete and can take a long time.
	 * Client side should set timeout accordingly. 
	 * Note: This is the old mailbox move request.
	 *       The new mailbox move request is MoveMailboxRequest.
	 * Network edition only API.
	 *
	 * @param  string  $account   Account email address. Account must exist and be provisioned on the local server.
	 * @param  string  $dest      Hostname of target server. Must differ from the account's host server.
	 * @param  integer $port      Target port for mailbox import.
	 * @param  string  $tempDir   Temporary directory to use on source server.
	 * @param  bool    $overwrite If this flag is set, the target mailbox will be replaced if it exists.
	 * @return mix
	 */
	function exportMailbox($account, $dest, $port = 0, $tempDir = '', $overwrite = FALSE);

	/**
	 * Failover Cluster Service.
	 * Network edition only API.
	 *
	 * @param  string $name      Cluster service name.
	 * @param  string $newServer New Server.
	 * @return mix
	 */
	function failoverClusterService($name, $newServer);

	/**
	 * Fix Calendar End Times.
	 *
	 * @param  string $account Accounts name.
	 * @param  bool   $sync    Sync flag.
	 * @return mix
	 */
	function fixCalendarEndTime($account, $sync = FALSE);

	/**
	 * Fix Calendar priority.
	 *
	 * @param  string $account Accounts name.
	 * @param  bool   $sync    Sync flag.
	 * @return mix
	 */
	function fixCalendarPriority($account, $sync = FALSE);

	/**
	 * Fix timezone definitions in appointments and tasks to reflect changes
	 * in daylight savings time rules in various timezones.
	 *
	 * @param  string $account Accounts name.
	 * @param  string $tzfixup Timezone fixup.
	 * @param  bool   $sync    Sync flag.
	 * @param  string $after   Fix appts/tasks that have instances after this time, default = January 1, 2008 00:00:00 in GMT+13:00 timezone.
	 * @return mix
	 */
	function fixCalendarTZ($account, array $tzfixup, $sync = FALSE, $after = '');

	/**
	 * FixFlush memory cache for specified LDAP or directory scan type/entries.
	 * Directory scan caches(source of data is on local disk of the server): skin|locale
	 * LDAP caches(source of data is LDAP): account|cos|domain|server|zimlet.
	 * 
	 * For LDAP caches, one or more optional <entry> can be specified. 
	 * If <entry>(s) are specified, only the specified entries will be flushed.
	 * If no <entry> is given, all enties of the type will be flushed from cache.
	 * Type can contain a combination of skin, locale and zimlet.
	 * E.g. type='skin,locale,zimlet' or type='zimletskin'.
	 *
	 * @param  array $types    Array of cache types. e.g. from skin|locale|account|cos|domain|server|zimlet.
	 * @param  array $entries Cache entry selectors.
	 * @param  bool  $all     All flag. 0 - flush cache only on the local server. 1 - flush cache only on all servers (can take a long time on systems with lots of servers)
	 * @return mix
	 */
	function flushCache(array $types = array('account'), array $entries = array(), $all = FALSE);

	/**
	 * Fix timezone definitions in appointments and tasks to reflect changes
	 * in daylight savings time rules in various timezones.
	 *
	 * @param  string  $server   Accounts name.
	 * @param  bool    $new      Sync flag.
	 * @param  integer $keysize  Key size.
	 * @param  string  $type     Type of CSR (self|comm).
	 * @param  array   $attrs    Subject attributes.
	 * @param  array   $altNames Used to add the Subject Alt Name extension in the certificate, so multiple hosts can be supported.
	 * @return mix
	 */
	function genCSR($server, $new, $keysize = 1024, $type = 'self', array $attrs = array(), array $altNames  = array());

	/**
	 * Get attributes related to an account.
	 * {request-attrs} - comma-seperated list of attrs to return 
	 * Note: this request is by default proxied to the account's home server 
	 * Access: domain admin sufficient
	 *
	 * @param  string $account  The name used to identify the account.
	 * @param  array  $attrs    List of attributes.
	 * @param  bool   $applyCos Flag whether or not to apply class of service (COS) rules.
	 * @return mix
	 */
	function getAccount($account, array $attrs = array(), $applyCos = TRUE);

	/**
	 * Get information about an account.
	 * Currently only 2 attrs are returned:
	 *   zimbraId	 the unique UUID of the zimbra account
	 *   zimbraMailHost	 the server on which this user's mail resides 
	 * Access: domain admin sufficient
	 *
	 * @param  string $account The name used to identify the account.
	 * @return mix
	 */
	function getAccountInfo($account);

	/**
	 * Returns custom loggers created for the given account since the last server start.
	 * If the request is sent to a server other than the one that the account resides on,
	 * it is proxied to the correct server.
	 *
	 * @param  string $account  The name used to identify the account.
	 * @return mix
	 */
	function getAccountLoggers($account);

	/**
	 * Get distribution lists an account is a member of.
	 *
	 * @param  string $account The name used to identify the account.
	 * @return mix
	 */
	function getAccountMembership($account);

	/**
	 * Get distribution lists an account is a member of.
	 *
	 * @param  string $account The name used to identify the account.
	 * @param  string $dl      The name used to identify the distribution list.
	 * @return mix
	 */
	function getAdminConsoleUIComp($account, $dl = '');

	/**
	 * Returns the admin extension addon Zimlets.
	 *
	 * @return mix
	 */
	function getAdminExtensionZimlets();

	/**
	 * Returns admin saved searches.
	 * If no <search> is present server will return all saved searches.
	 *
	 * @param  string $name The search name.
	 * @return mix
	 */
	function getAdminSavedSearches($name);

	/**
	 * Gets the aggregate quota usage for all domains on the server.
	 *
	 * @return mix
	 */
	function getAggregateQuotaUsageOnServer();

	/**
	 * Returns all account loggers that have been created on the given server
	 * since the last server start.
	 *
	 * @return mix
	 */
	function getAllAccountLoggers();

	/**
	 * Get All accounts matching the selectin criteria.
	 * Access: domain admin sufficient
	 *
	 * @param  string $server The server name.
	 * @param  string $domain The domain name.
	 * @return mix
	 */
	function getAllAccounts($server = '', $domain = '');

	/**
	 * Get all Admin accounts.
	 *
	 * @param  string $applyCos Apply COS.
	 * @return mix
	 */
	function getAllAdminAccounts($applyCos = TRUE);

	/**
	 * Get all calendar resources that match the selection criteria.
	 * Access: domain admin sufficient.
	 *
	 * @param  string $server The server name.
	 * @param  string $domain The domain name.
	 * @return mix
	 */
	function getAllCalendarResources($server = '', $domain = '');

	/**
	 * Get all config.
	 *
	 * @return mix
	 */
	function getAllConfig();

	/**
	 * Get all classes of service (COS).
	 *
	 * @return mix
	 */
	function getAllCos();

	/**
	 * Get all calendar resources that match the selection criteria.
	 * Access: domain admin sufficient.
	 *
	 * @param  string $domain The domain name.
	 * @return mix
	 */
	function getAllDistributionLists($domain = '');

	/**
	 * Get all domains.
	 *
	 * @param  bool $applyConfig Apply config flag.
	 * @return mix
	 */
	function getAllDomains($applyConfig = TRUE);

	/**
	 * Get all effective Admin rights.
	 *
	 * @param  string $grantee The name used to identify the grantee.
	 * @param  string $type    Type:usr|grp|egp|all|dom|gst|key|pub|email.
	 * @param  string $secret  Password for guest grantee or the access key for key grantee For user right only.
	 * @param  string $all     For GetGrantsRequest, selects whether to include grants granted to groups the specified grantee belongs to. Default is 1 (true).
	 * @param  string $expand  Flags whether to include all attribute names if the right is meant for all attributes.
	 * @return mix
	 */
	function getAllEffectiveRights($grantee = '', $type = 'all', $secret = '', $all = TRUE, $expand = '');

	/**
	 * Get all free/busy providers.
	 *
	 * @return mix
	 */
	function getAllFreeBusyProviders();

	/**
	 * Get all free/busy providers.
	 *
	 * @return mix
	 */
	function getAllLocales();

	/**
	 * Return all mailboxes.
	 * Returns all data from the mailbox table (in db.sql), except for the "comment" column.
	 *
	 * @param  integer $limit  The number of mailboxes to return (0 is default and means all).
	 * @param  integer $offset The starting offset (0, 25, etc).
	 * @return mix
	 */
	function getAllMailboxes($limit = 0, $offset = 0);

	/**
	 * Get all effective Admin rights.
	 *
	 * @param  string $type   Target type on which a right is grantable.
	 * @param  string $right  Right class to return (ADMIN|USER|ALL).
	 * @param  bool   $expand Flags whether to include all attribute names in the <attrs> elements in GetRightResponse if the right is meant for all attributes.
	 * @return mix
	 */
	function getAllRights($type, $right = 'ALL', $expand = TRUE);

	/**
	 * Get all servers defined in the system or all servers that
	 * have a particular service enabled (eg, mta, antispam, spell).
	 * If {apply} is 1 (true), then certain unset attrs on a server
	 * will get their value from the global config. 
	 * If {apply} is 0 (false), then only attributes directly set on the server will be returned
	 *
	 * @param  string $service Service name. e.g. mta, antispam, spell.
	 * @param  bool   $apply   Apply config flag.
	 * @return mix
	 */
	function getAllServers($service = 'mailbox', $apply = FALSE);

	/**
	 * Get all installed skins on the server.
	 *
	 * @return mix
	 */
	function getAllSkins();

	/**
	 * Returns all installed UC providers and applicable UC service attributes for each provider.
	 *
	 * @return mix
	 */
	function getAllUCProviders();

	/**
	 * Get all ucservices defined in the system.
	 *
	 * @return mix
	 */
	function getAllUCServices();

	/**
	 * Get all volumes.
	 *
	 * @return mix
	 */
	function getAllVolumes();

	/**
	 * Get all XMPP components.
	 *
	 * @return mix
	 */
	function getAllXMPPComponents();

	/**
	 * Get all Zimlets.
	 *
	 * @param  string $exclude Can be "none|extension|mail". extension: return only mail Zimlets. mail: return only admin extensions. none [default]: return both mail and admin zimlets.
	 * @return mix
	 */
	function getAllZimlets($exclude = 'none');

	/**
	 * Get Appliance HSM Filesystem information.
	 * Network edition only API.
	 *
	 * @return mix
	 */
	function getApplianceHSMFS();

	/**
	 * Get attribute information.
	 * Valid entry types:
	 *   account,alias,distributionList,cos,globalConfig,domain,server,mimeEntry,zimletEntry,
     *   calendarResource,identity,dataSource,pop3DataSource,imapDataSource,rssDataSource,
     *   liveDataSource,galDataSource,signature,xmppComponent,aclTarget
	 *
	 * @param  array $attrs      Attributes to return.
	 * @param  array $entryTypes Attributes on the specified entry types will be returned.
	 * @return mix
	 */
	function getAttributeInfo(array $attrs = array(), array $entryTypes = array());

	/**
	 * Get a certificate signing request (CSR).
	 *
	 * @param  string $server      Server ID. Can be "--- All Servers ---" or the ID of a server.
	 * @param  string $type Type of CSR (required). Value: self mean self-signed certificate; comm mean commercial certificate
	 * @return mix
	 */
	function getCSR($server = '', $type = 'self');

	/**
	 * Get a calendar resource.
	 * Access: domain admin sufficient.
	 *
	 * @param  string $account  The name used to identify the account.
	 * @param  bool   $applyCos Flag whether to apply Class of Service (COS).
	 * @param  array  $attrs    Array of attributes.
	 * @return mix
	 */
	function getCalendarResource($account = '', $applyCos = TRUE, array $attrs = array());

	/**
	 * Get Certificate.
	 * Currently, GetCertRequest/Response only handle 2 types "staged" and "all".
	 * May need to support other options in the future.
	 *
	 * @param  string $server The server's ID whose cert is to be got.
	 * @param  bool   $type   Certificate type. Value: staged - view the staged crt. Other options (all, mta, ldap, mailboxd, proxy) are used to view the deployed crt
	 * @param  array  $option Required only when type is "staged". Could be "self" (self-signed cert) or "comm" (commerical cert).
	 * @return mix
	 */
	function getCert($server, $type = 'all', $option = '');

	/**
	 * Get Cluster Status.
	 * Network edition only API.
	 *
	 * @return mix
	 */
	function getClusterStatus();

	/**
	 * Get Config request.
	 *
	 * @param  array $attrs Array of attributes.
	 * @return mix
	 */
	function getConfig(array $attrs = array());

	/**
	 * Get Class Of Service (COS).
	 *
	 * @param  string $cos   The name used to identify the COS.
	 * @param  array  $attrs Array of attributes.
	 * @return mix
	 */
	function getCos($cos = '', array $attrs = array());

	/**
	 * Returns attributes, with defaults and constraints if any,
	 * that can be set by the authed admin when an object is created.
	 * Domain name.required if target type is account/calresource/dl/domain, ignored otherwise.
	 *   1. if {target-type} is account/calresource/dl:
	 *      This is the domain in which the object will be in.
	 *      The domain can be speciffied by id or by name.
	 *   2. if {target-type} is domain, it is the domain name to be created.
	 *      E.g. to create a subdomain named foo.bar.test.com,
	 *      should pass in <domain by="name">foo.bar.test.com</domain>.
	 *
	 * @param  string $type   Target type. Valid values: (userAccount|account|alias|dl|domain|cos|server|calresource|accountOnUCService|cosOnUCService|domainOnUCService|internalUserAccount|internalArchivingAccount)
	 * @param  string $target Target.
	 * @param  string $domain The name used to identify the domain.
	 * @param  string $cos    The name used to identify the COS..
	 * @return mix
	 */
	function getCreateObjectAttrs($type, $target = '', $domain = '', $cos = '');

	/**
	 * Get current volumes.
	 *
	 * @return mix
	 */
	function getCurrentVolumes();

	/**
	 * Returns all data sources defined for the given mailbox.
	 * For each data source, every attribute value is returned except password.
	 * Note: this request is by default proxied to the account's home server.
	 *
	 * @param  string $id    Account ID for an existing account.
	 * @param  array  $attrs Array of attributes.
	 * @return mix
	 */
	function getDataSources($id, array $attrs = array());

	/**
	 * Get constraints (zimbraConstraint) for delegated admin on global config or a COS
	 * none or several attributes can be specified for which constraints are to be returned.
	 * If no attribute is specified, all constraints on the global config/cos will be returned.
	 * If there is no constraint for a requested attribute,
	 * <a> element for the attribute will not appear in the response.
	 *
	 * @param  string $type  Target type. Valid values: (account|calresource|cos|dl|group|domain|server|ucservice|xmppcomponent|zimlet|config|global).
	 * @param  string $name  Name of target.
	 * @param  string $id    ID of target.
	 * @param  array  $attrs Array of name.
	 * @return mix
	 */
	function getDelegatedAdminConstraints($type, $name = '', $id = '', array $attrs = array());

	/**
	 * Get the requested device's status.
	 * Network edition only API.
	 *
	 * @param  string $account The name used to identify the account.
	 * @param  string $device  Device ID.
	 * @return mix
	 */
	function getDeviceStatus($account, $device = '');

	/**
	 * Get devices.
	 *
	 * @param  string $account The name used to identify the account.
	 * @return mix
	 */
	function getDevices($account);

	/**
	 * Get the registered devices count on the server.
	 * Network edition only API.
	 *
	 * @return mix
	 */
	function getDevicesCount();

	/**
	 * Get the mobile devices count on the server since last used date.
	 * Network edition only API.
	 *
	 * @param  string $date Last used date. Date in format: yyyy-MM-dd.
	 * @return mix
	 */
	function getDevicesCountSinceLastUsed($date);

	/**
	 * Get the mobile devices count on the server used today.
	 * Network edition only API.
	 *
	 * @return mix
	 */
	function getDevicesCountUsedToday();

	/**
	 * Get a Distribution List.
	 *
	 * @param  string  $dl     The name used to identify the distribution list.
	 * @param  array   $attrs  Attributes.
	 * @param  integer $limit  The maximum number of accounts to return (0 is default and means all).
	 * @param  integer $offset The starting offset (0, 25 etc).
	 * @param  bool    $asc    Flag whether to sort in ascending order 1 (true) is the default.
	 * @return mix
	 */
	function getDistributionList($dl = '', array $attrs = array(), $limit = 0, $offset = 0, $asc = TRUE);

	/**
	 * Request a list of DLs that a particular DL is a member of.
	 *
	 * @param  string  $dl     The name used to identify the distribution list.
	 * @param  integer $limit  The maximum number of DLs to return (0 is default and means all).
	 * @param  integer $offset The starting offset (0, 25 etc).
	 * @return mix
	 */
	function getDistributionListMembership($dl = '', $limit = 0, $offset = 0);

	/**
	 * Get information about a domain.
	 * 
	 * @param  string $domain The name used to identify the domain.
	 * @param  bool   $apply  Apply config flag. True, then certain unset attrs on a domain will get their values from the global config. False, then only attributes directly set on the domain will be returned.
	 * @param  array  $attrs  Attributes.
	 * @return mix
	 */
	function getDomain($domain = '', $apply = TRUE, array $attrs = array());

	/**
	 * Get Domain information.
	 * This call does not require an auth token.
	 * It returns attributes that are pertinent to domain settings
	 * for cases when the user is not authenticated.
	 * For example, URL to direct the user to upon logging out or when auth token is expired.
	 * 
	 * @param  string $domain The name used to identify the domain.
	 * @param  bool   $apply  Apply config flag. True, then certain unset attrs on a domain will get their values from the global config. False, then only attributes directly set on the domain will be returned.
	 * @return mix
	 */
	function getDomainInfo($domain = '', $apply = TRUE);

	/**
	 * Returns effective ADMIN rights the authenticated admin has on the specified target entry.
	 * Effective rights are the rights the admin is actually allowed.
	 * It is the net result of applying ACL checking rules given the target and grantee.
	 * Specifically denied rights will not be returned.
	 * 
	 * @param  string $target  The name used to identify the target.
	 * @param  string $type    Target type. Valid values: (account|calresource|cos|dl|group|domain|server|ucservice|xmppcomponent|zimlet|config|global).
	 * @param  array  $grantee Grantee.
	 * @param  string $expand  Whether to include all attribute names in the <getAttrs>/<setAttrs> elements in the response if all attributes of the target are gettable/settable.
	 *                         Valid values are:
	 *                         1. getAttrs: expand attrs in getAttrs in the response
	 *                         2. setAttrs: expand attrs in setAttrs in the response
	 *                         3. getAttrs,setAttrs: expand attrs in both getAttrs and setAttrs in the response
	 * @return mix
	 */
	function getEffectiveRights($target, $type, array $grantee = array(), $expand = '');

	/**
	 * Get Free/Busy provider information.
	 * If the optional element <provider/> is present in the request, the response contains the requested provider only.
	 * If no provider is supplied in the request, the response contains all the providers.
	 * 
	 * @param  string $name Provider name.
	 * @return mix
	 */
	function getFreeBusyQueueInfo($name);

	/**
	 * Returns all grants on the specified target entry,
	 * or all grants granted to the specified grantee entry. 
	 * The authenticated admin must have an effective "viewGrants"
	 * (TBD) system right on the specified target/grantee. 
	 * At least one of <target> or <grantee> must be specified.
	 * If both <target> and <grantee> are specified, only grants that are granted
	 * on the target to the grantee are returned.
	 * 
	 * @param  string $target  The name used to identify the target.
	 * @param  string $type    Target type. Valid values: (account|calresource|cos|dl|group|domain|server|ucservice|xmppcomponent|zimlet|config|global).
	 * @param  array  $grantee Grantee.
	 * @return mix
	 */
	function getGrants($target, $type, array $grantee = array());

	/**
	 * Queries the status of the most recent HSM session.
	 * Status information for a given HSM session is available until
	 * the next time HSM runs or until the server is restarted.
	 * Notes:
	 *   1. If an HSM session is running, "endDate" is not specified in the response.
	 *   2. As an HSM session runs, numMoved and numMailboxes increase with subsequent requests.
	 *   3. A response sent while HSM is aborting returns aborted="0" and aborting="1".
	 *   4. If HSM completed successfully, numMailboxes == totalMailboxes.
	 *   5. If <GetHsmStatusRequest> is sent after a server restart but before an HSM session,
	 *      the response will contain running="0" and no additional information.
	 *   6. Once HSM completes, the same <GetHsmStatusResponse> will be returned until
	 *      another HSM session or a server restart.
	 * Network edition only API.
	 * 
	 * @return mix
	 */
	function getHsmStatus();

	/**
	 * Get index statistics.
	 * 
	 * @param  string $id  Account ID.
	 * @return mix
	 */
	function getIndexStats($id);

	/**
	 * Get index statistics.
	 * 
	 * @param  string  $query  Query string. Should be an LDAP-style filter string (RFC 2254).
	 * @param  string  $base   LDAP search base. An LDAP-style filter string that defines an LDAP search base (RFC 2254).
	 * @param  integer $limit  Limit - the maximum number of LDAP objects (records) to return (0 is default and means all).
	 * @param  integer $offset The starting offset (0, 25, etc).
	 * @param  string  $sort   Name of attribute to sort on. default is null.
	 * @param  bool    $asc    Flag whether to sort in ascending order 1 (true) is default.
	 * @return mix
	 */
	function getLDAPEntries($query, $base, $limit = 0, $offset = 0, $sort = NULL, $asc = TRUE);

	/**
	 * Get License.
	 * Network edition only API.
	 * 
	 * @return mix
	 */
	function getLicense();

	/**
	 * Get License information.
	 * 
	 * @return mix
	 */
	function getLicenseInfo();

	/**
	 * Query to retrieve Logger statistics in ZCS.
	 * Use cases:
	 *   1. No elements specified. Result: a listing of reporting host names.
	 *   2. Hostname specified. Result: a listing of stat groups for the specified host.
	 *   3. Hostname and stats specified, text content of stats non-empty.
	 *      Result: a listing of columns for the given host and group
	 *   4. Hostname and stats specified, text content empty, startTime/endTime optional.
	 *      Result: all of the statistics for the given host/group are returned,
	 *      if start and end are specified, limit/expand the timerange to the given setting.
     *      If limit=true is specified, attempt to reduce result set to under 500 records
	 * 
	 * @param  string $hostname  Hostname.
	 * @param  string $startTime Start time.
	 * @param  string $endTime   End time .
	 * @param  array  $stats     Stats specification.
	 * @return mix
	 */
	function getLoggerStats($hostname, $startTime, $endTime, array $stats = array());

	/**
	 * Summarize and/or search a particular mail queue on a particular server.
	 * The admin SOAP server initiates a MTA queue scan (via ssh)
	 * and then caches the result of the queue scan.
	 * To force a queue scan, specify scan=1 in the request.
	 * The response has two parts.
	 *   1. <qs> elements summarize queue by various types of data (sender addresses,
	 *      recipient domain, etc). Only the deferred queue has error summary type.
	 *   2. <qi> elements list the various queue items that match the requested query.
	 * The stale-flag in the response means that since the scan,
	 * some queue action was done and the data being presented is now stale.
     * This allows us to let the user dictate when to do a queue scan.
     * The scan-flag in the response indicates that the server has not completed scanning
     * the MTA queue, and that this scan is in progress,
     * and the client should ask again in a little while. 
     * The more-flag in the response indicates that more qi's are available past the limit specified in the request.
	 * 
	 * @param  string  $server Server Mail Queue Query.
	 * @param  string  $queue  Queue name.
	 * @param  array   $query  Query.
	 * @param  array   $field  Queue query field.
	 * @param  bool    $scan   To fora a queue scan, set this to 1 (true).
	 * @param  integer $wait   Maximum time to wait for the scan to complete in seconds (default 3).
	 * @param  integer $limit  Limit the number of queue items to return in the response.
	 * @param  integer $offset Offset - the starting offset (0, 25, etc).
	 * @return mix
	 */
	function  getMailQueue($server, $queue, array $field = array(), $scan = TRUE, $wait = 3, $limit = 0, $offset = 0);

	/**
	 * Get a count of all the mail queues by counting the number of files in the queue directories.
	 * Note that the admin server waits for queue counting to complete before responding
	 * - client should invoke requests for different servers in parallel.
	 * 
	 * @param  string $server MTA server name.
	 * @return mix
	 */
	function getMailQueueInfo($server);

	/**
	 * Get a Mailbox.
	 * Note: this request is by default proxied to the account's home server.
	 * 
	 * @param  string $id Account ID.
	 * @return mix
	 */
	function getMailbox($id);

	/**
	 * Get MailBox Statistics.
	 * 
	 * @return mix
	 */
	function getMailboxStats();

	/**
	 * Returns the version info for a mailbox.
	 * Mailbox move uses this request to prevent a move to an older server.
	 * Network edition only API.
	 * 
	 * @param  string $account Account email address.
	 * @return mix
	 */
	function getMailboxVersion($account);

	/**
	 * Returns the info on blob and index volumes of a mailbox.
	 * Only the volumes that have data for the mailbox are returned.
	 * The rootpath attribute is the root of the mailbox data, rather than the root of the volume.
	 * Also returns the current sync token of the mailbox.
	 * Network edition only API.
	 * 
	 * @param  string $account Account email address.
	 * @return mix
	 */
	function getMailboxVolumes($account);

	/**
	 * Returns the memcached client configuration on a mailbox server.
	 * 
	 * @return mix
	 */
	function getMemcachedClientConfig();

	/**
	 * Returns the memcached client configuration on a mailbox server.
	 * 
	 * @param  string  $domain  Domain - the domain name to limit the search to.
	 * @param  bool    $all     Whether to fetch quota usage for all domain accounts from across all mailbox servers, default is false, applicable when domain attribute is specified.
	 * @param  integer $limit   Limit - the number of accounts to return (0 is default and means all).
	 * @param  integer $offset  Offset - the starting offset (0, 25, etc).
	 * @param  string  $sort    SortBy - valid values: "percentUsed", "totalUsed", "quotaLimit".
	 * @param  bool    $asc     Whether to sort in ascending order 0 (false) is default, so highest quotas are returned first.
	 * @param  bool    $refresh Refresh - whether to always recalculate the data even when cached values are available. 0 (false) is the default..
	 * @return mix
	 */
	function getQuotaUsage($domain = '', $all = TRUE, $limit = 0, $offset = 0, $sort = 'percentUsed', $asc = FALSE, $refresh = FALSE);

	/**
	 * Get definition of a right.
	 * 
	 * @param  string $right  Right name.
	 * @param  bool   $expand Whether to include all attribute names in the <attrs> elements in the response if the right is meant for all attributes.
	 *                        0 (false) [default] default, do not include all attribute names in the <attrs> elements.
	 *                        1 (true)  include all attribute names in the <attrs> elements.
	 * @return mix
	 */
	function getRight($right, $expand = FALSE);

	/**
	 * Get Rights Document.
	 * 
	 * @param  array $packages Packages.
	 * @return mix
	 */
	function getRightsDoc(array $packages = array());

	/**
	 * Get a configuration for SMIME public key lookup via external LDAP on a domain or globalconfig.
	 * Notes: if <domain> is present, get the config on the domain,
	 * otherwise get the config on globalconfig.
	 * Network edition only API.
	 * 
	 * @param  string $name   Config name.
	 * @param  string $domain Domain name.
	 * @return mix
	 */
	function getSMIMEConfig($name = '', $domain = '');

	/**
	 * Get Server.
	 * 
	 * @param  string $server Server name.
	 * @param  array  $attrs  Attributes.
	 * @param  bool   $apply  Apply config flag.
	 *                        If {apply} is 1 (true), then certain unset attrs on a server will get their values from the global config. 
	 *                        if {apply} is 0 (false), then only attributes directly set on the server will be returned.
	 * @return mix
	 */
	function getServer($server, array $attrs = array(), $apply = TRUE);

	/**
	 * Get Network Interface information for a server.
	 * Get server's network interfaces. Returns IP addresses and net masks.
	 * This call will use zmrcd to call /opt/zimbra/libexec/zmserverips
	 * 
	 * @param  string $server Server name.
	 * @param  string $type   Specifics the ipAddress type (ipV4/ipV6/both). default is ipv4.
	 * @return mix
	 */
	function getServerNIfs($server, $type = 'ipV4');

	/**
	 * Returns server monitoring stats.
	 * These are the same stats that are logged to mailboxd.csv.
	 * If no <stat> element is specified, all server stats are returned.
	 * If the stat name is invalid, returns a SOAP fault.
	 * 
	 * @param  array $stats Stats.
	 * @return mix
	 */
	function getServerStats(array $stats = array());

	/**
	 * Get Service Status.
	 * 
	 * @return mix
	 */
	function getServiceStatus();

	/**
	 * Get Sessions.
	 * Access: domain admin sufficient (though a domain admin can't specify "domains" as a type).
	 * 
	 * @param  string  $type    Type - valid values soap|imap|admin.
	 * @param  string  $sortBy  Sort by - valid values nameAsc|nameDesc|createdAsc|createdDesc|accessedAsc|accessedDesc.
	 * @param  integer $limit   Limit - the number of sessions to return per page (0 is default and means all).
	 * @param  integer $offset  Offset - the starting offset (0, 25, etc).
	 * @param  bool    $refresh Refresh. If 1 (true), ignore any cached results and start fresh..
	 * @return mix
	 */
	function getSessions($type, $sortBy = 'nameAsc', $limit = 0, $offset = 0, $refresh = TRUE);

	/**
	 * Iterate through all folders of the owner's mailbox and return shares
	 * that match grantees specified by the <grantee> specifier.
	 * 
	 * @param  string $owner The name used to identify the account.
	 * @param  string $type  If specified, filters the result by the specified grantee type.
	 * @param  string $name  If specified, filters the result by the specified grantee name.
	 * @param  string $id    If specified, filters the result by the specified grantee ID.
	 * @return mix
	 */
	function getShareInfo($owner, $type = '', $name = '', $id = '');

	/**
	 * Get System Retention Policy.
	 * The system retention policy SOAP APIs allow the administrator
	 * to edit named system retention policies that users can apply to folders and tags.
	 * 
	 * @param  string $cos The name used to identify the COS.
	 * @return mix
	 */
	function getSystemRetentionPolicy($cos = '');

	/**
	 * Get UC Service.
	 * 
	 * @param  string $ucservice UC Service name.
	 * @param  array  $attrs     Attributes.
	 * @return mix
	 */
	function getUCService($ucservice = '', array $attrs = array());

	/**
	 * Get Version information.
	 * 
	 * @return mix
	 */
	function getVersionInfo();

	/**
	 * Get Volume.
	 * 
	 * @param  integer $id ID of volume.
	 * @return mix
	 */
	function getVolume($id);

	/**
	 * Get Volume.
	 * 
	 * @param  string $xmpp  The name used to identify the XMPP component.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function getXMPPComponent($xmpp, array $attrs = array());

	/**
	 * Retreives a list of search tasks running or cached on a server.
	 * Network edition only API.
	 * 
	 * @return mix
	 */
	function getXMbxSearchesList();

	/**
	 * Retreives a list of search tasks running or cached on a server.
	 * 
	 * @param  string $name  Zimlet name.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function getZimlet($name, array $attrs = array());

	/**
	 * Get status for Zimlets.
	 * Priority is listed in the global list <zimlets> ... </zimlets> only.
	 * This is because the priority value is relative to other Zimlets in the list.
	 * The same Zimlet may show different priority number depending
	 * on what other Zimlets priorities are.
	 * The same Zimlet will show priority 0 if all by itself,
	 * or priority 3 if there are three other Zimlets with higher priority.
	 * 
	 * @return mix
	 */
	function getZimletStatus();

	/**
	 * Grant a right on a target to an individual or group grantee.
	 * 
	 * @param  string $target  Target selector. The name used to identify the target.
	 * @param  string $type    Target type. Valid values: (account|calresource|cos|dl|group|domain|server|ucservice|xmppcomponent|zimlet|config|global).
	 * @param  array  $grantee Grantee selector.
	 * @param  array  $right   Right selector.
	 * @return mix
	 */
	function grantRight($target, $type, array $grantee, array $right);

	/**
	 * Starts the HSM process, which moves blobs for older messages
	 * to the current secondary message volume.
	 * This request is asynchronous.
	 * The progress of the last HSM process can be monitored with GetHsmStatusRequest.
	 * The HSM policy is read from the zimbraHsmPolicy LDAP attribute.
	 * Network edition only API
	 * 
	 * @return mix
	 */
	function hsm();

	/**
	 * Ask server to install the certificates.
	 * Network edition only API
	 * 
	 * @param  string  $server    Server ID.
	 * @param  string  $type      Certificate type. Could be "self" (self-signed cert) or "comm" (commerical cert).
	 * @param  array   $comm_cert Commercial certificate.
	 * @param  array   $subject   Subject.
	 * @param  integer $validDays Number of the validation days of the self signed certificate.
	 * @param  array   $altNames  subjectAltNames.
	 * @param  integer $keysize   Key Size: 1024|2048, key length of the self-signed certificate.
	 * @return mix
	 */
	function installCert($server, $type = 'self', array $comm_cert = array(), array $subject = array(), $validDays = 0, array $altNames = array(), $keysize = 1024);

	/**
	 * Install a license.
	 * Network edition only API
	 * 
	 * @param  string $aid Attachment ID.
	 * @return mix
	 */
	function installLicense($aid);

	/**
	 * Command to act on invidual queue files.
	 * This proxies through to postsuper.
	 * list-of-ids can be ALL.
	 * 
	 * @param  string $server MTA server.
	 * @param  string $queue  Queue name.
	 * @param  string $op     Action operation. Valid values: (hold|release|delete|requeue).
	 * @param  string $by     Action by selector (id|query). (id) Body contains a list of ids. (query) Body contains a query element
	 * @param  array  $query  Queue query
	 * @param  array  $field  Queue query field
	 * @return mix
	 */
	function mailQueueAction($server, $queue, $op, $by, array $query = array(), array $field = array());

	/**
	 * Command to invoke postqueue -f.
	 * All queues cached in the server are stale after invoking this because
	 * this is a global operation to all the queues in a given server.
	 * 
	 * @param  string $server MTA server.
	 * @return mix
	 */
	function mailQueueFlush($server);

	/**
	 * Migrate an account.
	 * 
	 * @param  string $id     Zimbra ID of account.
	 * @param  string $action Action.
	 * @return mix
	 */
	function migrateAccount($id, $action);

	/**
	 * Modify an account.
	 * 
	 * @param  string $id    Zimbra ID of account.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function modifyAccount($id, array $attrs = array());

	/**
	 * Modifies admin saved searches.
	 * Returns the admin saved searches.
	 * If {search-query} is empty => delete the search if it exists.
	 * If {search-name} already exists => replace with new {search-query}.
	 * If {search-name} does not exist => save as a new search.
	 * 
	 * @param  array $searchs Array of search.
	 * @return mix
	 */
	function modifyAdminSavedSearches(array $searchs = array());

	/**
	 * Modify a calendar resource.
	 * Notes:
	 *   1. an empty attribute value removes the specified attr.
	 *   2. this request is by default proxied to the resources's home server.
	 * Access: domain admin sufficient. limited set of attributes that can be updated by a domain admin.
	 * 
	 * @param  string $id    Zimbra ID.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function modifyCalendarResource($id, array $attrs = array());

	/**
	 * Modify Configuration attributes.
	 * Note: an empty attribute value removes the specified attr.
	 * 
	 * @param  array $attrs Attributes.
	 * @return mix
	 */
	function modifyConfig(array $attrs = array());

	/**
	 * Modify Class of Service (COS) attributes.
	 * Note: an empty attribute value removes the specified attr.
	 * 
	 * @param  string $id    Zimbra ID.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function modifyCos($id, array $attrs = array());

	/**
	 * Changes attributes of the given data source.
	 * Only the attributes specified in the request are modified.
	 * To change the name, specify "zimbraDataSourceName" as an attribute.
	 * Note: this request is by default proxied to the account's home server
	 * 
	 * @param  string $id         Existing account ID.
	 * @param  string $dataSource Data source  ID.
	 * @param  array  $attrs      Attributes.
	 * @return mix
	 */
	function modifyDataSource($id, $dataSource, array $attrs = array());

	/**
	 * Modify constraint (zimbraConstraint) for delegated admin on global config or a COS.
	 * If constraints for an attribute already exists, it will be replaced by the new constraints.
	 * I <constraint> is an empty element, constraints for the attribute will be removed.
	 * 
	 * @param  string $type  Target type. Valid values: (account|calresource|cos|dl|group|domain|server|ucservice|xmppcomponent|zimlet|config|global).
	 * @param  string $id    ID.
	 * @param  string $name  Name.
	 * @param  array  $attrs Constaint attributes.
	 * @return mix
	 */
	function modifyDelegatedAdminConstraints($type, $id, $name, array $attrs = array());

	/**
	 * Modify attributes for a Distribution List.
	 * Notes: an empty attribute value removes the specified attr.
	 * Access: domain admin sufficient.
	 * 
	 * @param  string $id    Zimbra ID.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function modifyDistributionList($id, array $attrs = array());

	/**
	 * Modify attributes for a domain.
	 * Note: an empty attribute value removes the specified attr.
	 * 
	 * @param  string $id    Zimbra ID.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function modifyDomain($id, array $attrs = array());

	/**
	 * Modify an LDAP Entry.
	 * 
	 * @param  string $dn    A valid LDAP DN String (RFC 2253) that identifies the LDAP object.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function modifyLDAPEntry($dn, array $attrs = array());

	/**
	 * Modify a configuration for SMIME public key lookup via external LDAP on a domain or globalconfig.
	 * Notes: if <domain> is present, modify the config on the domain,
	 *        otherwise modify the config on globalconfig.
	 * Network edition only API.
	 * 
	 * @param  string $name   Config name.
	 * @param  string $op     Operation.
	 *   1. (modify) modify the SMIME config: modify/add/remove specified attributes of the config.
     *   2. (remove) remove the SMIME config: remove all attributes of the config. Must not include an attr map under the <config> element.
	 * @param  array  $attrs  Attributes.
	 * @param  string $domain Domain selector.
	 * @return mix
	 */
	function modifySMIMEConfig($name, $op = 'modify', array $attrs = array(), $domain = '');

	/**
	 * Modify attributes for a server.
	 * Notes:
	 *   1. An empty attribute value removes the specified attr.
	 *   2. His request is by default proxied to the referenced server.
	 * 
	 * @param  string $id    Zimbra ID.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function modifyServer($id, array $attrs = array());

	/**
	 * Modify system retention policy.
	 * 
	 * @param  array  $policy New policy.
	 * @param  string $cos    The name used to identify the COS.
	 * @return mix
	 */
	function modifySystemRetentionPolicy(array $policy, $cos = '' );

	/**
	 * Modify attributes for a UC service.
	 * Notes: An empty attribute value removes the specified attr
	 * 
	 * @param  string $id    Zimbra ID.
	 * @param  array  $attrs Attributes.
	 * @return mix
	 */
	function modifyUCService($id, array $attrs = array());

	/**
	 * Modify volume.
	 * 
	 * @param  string $id     Zimbra ID.
	 * @param  array  $volume Volume information.
	 * @return mix
	 */
	function modifyVolume($id, array $volume = array());

	/**
	 * Modify Zimlet.
	 * 
	 * @param  string  $name     Zimlet name.
	 * @param  string  $cos      Name of Class Of Service (COS).
	 * @param  string  $acl      Acl (grant or deny).
	 * @param  string  $status   Status - valid values for valueattribute - enabled|disabled.
	 * @param  integer $priority Priority.
	 * @return mix
	 */
	function modifyZimlet($name, $cos = '', $acl = 'grant', $status = 'enabled', $priority = 0);

	/**
	 * Moves blobs between volumes.
	 * Unlike HsmRequest, this request is synchronous,
	 * and reads parameters from the request attributes instead of zimbraHsmPolicy.
	 * Network edition only API.
	 * 
	 * @param  string  $types    Array of item types, or "all" for all types.
	 * @param  string  $sources  Array of source volume IDs.
	 * @param  string  $dest     Destination volume ID.
	 * @param  integer $maxBytes Limit for the total number of bytes of data to move. Blob move will abort if this threshold is exceeded.
	 * @param  string  $query    Query - if specified, only items that match this query will be moved.
	 * @return mix
	 */
	function moveBlobs(array $types, array $sources, $dest, $maxBytes = 0, $query = '');

	/**
	 * Move a mailbox.
	 * Note: This request should be sent to the move destination server, rather than the source server.
	 * Moves the mailbox of the specified account to this host.
	 * The src and dest attributes are required as safety checks.
	 * src must be set to the current home server of the account,
	 * and dest must be set to the server receiving the request.
	 * Network edition only API.
	 * 
	 * @param  string  $name        Account email address.
	 * @param  string  $dest        Hostname of target server.
	 * @param  string  $src         Hostname of source server.
	 * @param  string  $blobs       Option to include/exclude blobs in a move - include|exclude|config. Default value is "config", to use the configured value. "include" or "exclude" overrides the configuration.
	 * @param  string  $secondBlobs Option to include/exclude secondary blobs in a move - include|exclude|config. Default value is "config", to use the configured value. "include" or "exclude" overrides the configuration. Meaningful only when blobs isn't excluded.
	 * @param  string  $searchIndex Option to include/exclude searchIndex in a move - include|exclude|config. Default value is "config", to use the configured value. "include" or "exclude" overrides the configuration.
	 * @param  integer $maxSyncs    Maximum number of syncs. Default is 10.
	 * @param  integer $threshold   Sync finish threshold. Default is 30000 (30 seconds).
	 * @param  bool    $sync        If set, run synchronously; command doesn't return until move is finished.
	 * @return mix
	 */
	function moveMailbox($name, $dest, $src, $blobs = 'config', $secondBlobs = 'config', $searchIndex = 'config', $maxSyncs = 10, $threshold = 30000, $sync = FALSE);

	/**
	 * A request that does nothing and always returns nothing.
	 * Used to keep an admin session alive.
	 * 
	 * @return mix
	 */
	function noOp();

	/**
	 * Ping.
	 * 
	 * @return mix
	 */
	function ping();

	/**
	 * Purge the calendar cache for an account.
	 * Access: domain admin sufficient.
	 * 
	 * @param  string $id Zimbra ID.
	 * @return mix
	 */
	function purgeAccountCalendarCache($id);

	/**
	 * Purges the queue for the given freebusy provider on the current host.
	 * 
	 * @param  string $name Provider name.
	 * @return mix
	 */
	function purgeFreeBusyQueue($name);

	/**
	 * Purges aged messages out of trash, spam, and entire mailbox.
	 * (if <mbox> element is omitted, purges all mailboxes on server).
	 * 
	 * @param  string $id Account ID.
	 * @return mix
	 */
	function purgeMessages($id);

	/**
	 * Purge moved mailbox.
	 * Following a successful mailbox move to a new server, the mailbox on the old server remains.
	 * This allows manually checking the new mailbox to confirm the move worked.
	 * Afterwards, PurgeMovedMailboxRequest should be used to remove
	 * the old mailbox and reclaim the space.
	 * Network edition only API.
	 * 
	 * @param  string $name Mailbox name.
	 * @return mix
	 */
	function purgeMovedMailbox($name);

	/**
	 * Push Free/Busy.
	 * The request must include either <domain/> or <account/>.
	 * When <domain/> is specified in the request, the server will push
	 * the free/busy for all the accounts in the domain to the configured free/busy providers.
	 * When <account/> list is specified, the server will push the free/busy for
	 * the listed accounts to the providers.
	 * 
	 * @param  array  $domains Domain names specification.
	 * @param  string $account Account ID.
	 * @return mix
	 */
	function pushFreeBusy(array $domains = array(), $account = '');

	/**
	 * Show mailbox moves in progress on this server.
	 * Both move-ins and move-outs are shown.
	 * If accounts are given only data for those accounts are returned.
	 * Data for all moves are returned if no accounts are given. 
	 * If checkPeer=1 (true), peer servers are queried to check if the move is active on the peer. [default 0 (false)].
	 * Network edition only API.
	 * 
	 * @param  string $name      Array of account name.
	 * @param  bool   $checkPeer Flag whether to query peer servers to see if the move is active on them. [default 0 (false)]
	 * @return mix
	 */
	function queryMailboxMove(array $accounts = array(), $checkPeer = FALSE);

	/**
	 * Query WaitSet.
	 * This API dumps the internal state of all active waitsets.
	 * It is intended for debugging use only and should not be used for production uses.
	 * This API is not guaranteed to be stable between releases in any way
	 * and might be removed without warning.
	 * 
	 * @param  string $waitSet WaitSet ID.
	 * @return mix
	 */
	function queryWaitSet($waitSet);

	/**
	 * ReIndex.
	 * Access: domain admin sufficient.
	 * Note: This request is by default proxied to the account's home server.
	 * Note: Only one of {ids} and {types} may be specified.
	 * 
	 * @param  string $id     Account ID.
	 * @param  string $action Action to perform. start: (start) reindexing. (status): show reindexing progress. (cancel): cancel reindexing.
	 * @param  string $types  Array of types. Valid values: conversation|message|contact|appointment|task|note|wiki|document.
	 * @param  string $ids    Array of IDs to re-index.
	 * @return mix
	 */
	function reIndex($id, $action = 'status', array $types = array(), array $ids = array());

	/**
	 * Recalculate Mailbox counts.
	 * Forces immediate recalculation of total mailbox quota usage and all folder unread
	 * and size counts.
	 * Access: domain admin sufficient.
	 * Note: this request is by default proxied to the account's home server.
	 * 
	 * @param  string $id Account ID.
	 * @return mix
	 */
	function recalculateMailboxCounts($id);

	/**
	 * Register Mailbox move out.
	 * This request is invoked by move destination server against move source server
	 * to signal the start of a mailbox move.
	 * The receiving server registers a move-out.
	 * This helps prevent simultaneous moves of the same mailbox.
	 * Network edition only API.
	 * 
	 * @param  string $name Account email address.
	 * @param  string $dest Hostname of destination server.
	 * @return mix
	 */
	function registerMailboxMoveOut($name, $dest);

	/**
	 * Reload Account.
	 * Called after another server has made changes to the account object,
	 * this request tells the server to reload the account object from
	 * the ldap master to pick up the changes.
	 * Network edition only API.
	 * 
	 * @param  string $name Account email address.
	 * @return mix
	 */
	function reloadAccount($name);

	/**
	 * Reload LocalConfig.
	 * 
	 * @return mix
	 */
	function reloadLocalConfig();

	/**
	 * Reloads the memcached client configuration on this server.
	 * Memcached client layer is reinitialized accordingly.
	 * Call this command after updating the memcached server list, for example.
	 * 
	 * @return mix
	 */
	function reloadMemcachedClientConfig();

	/**
	 * Request a device (e.g. a lost device) be wiped of all its data on the next sync.
	 * Network edition only API.
	 * 
	 * @param  string $account  Account email address.
	 * @param  string $deviceId Device ID.
	 * @return mix
	 */
	function remoteWipe($account, $deviceId = '');

	/**
	 * Remove Account Alias.
	 * Access: domain admin sufficient.
	 * Note: this request is by default proxied to the account's home server.
	 * 
	 * @param  string $alias Account alias.
	 * @param  string $id    Zimbra ID.
	 * @return mix
	 */
	function removeAccountAlias($alias, $id = '');

	/**
	 * Removes one or more custom loggers.
	 * If both the account and logger are specified, removes the given account logger if it exists.
	 * If only the account is specified or the category is "all",
	 * removes all custom loggers from that account.
	 * If only the logger is specified, removes that custom logger from all accounts.
	 * If neither element is specified, removes all custom loggers from all accounts
	 * on the server that receives the request.
	 * 
	 * @param  string $account  The name used to identify the account.
	 * @param  string $category Name of the logger category.
	 * @param  string $level    Level of the logging (error|warn|info|debug|trace).
	 * @return mix
	 */
	function removeAccountLogger($account, $category = '', $level = '');

	/**
	 * Remove a device or remove all devices attached to an account.
	 * This will not cause a reset of sync data, but will cause a reset of policies on the next sync.
	 * 
	 * @param  string $account  Account email address.
	 * @param  string $deviceId Device ID. Note - if not supplied ALL devices will be removed.
	 * @return mix
	 */
	function removeDevice($account, $deviceId = '');

	/**
	 * Remove Distribution List Alias.
	 * Access: domain admin sufficient.
	 * 
	 * @param  string $id    Zimbra ID
	 * @param  string $alias Distribution list alias.
	 * @return mix
	 */
	function removeDistributionListAlias($id, $alias);

	/**
	 * Remove Distribution List Member.
	 * Unlike add, remove of a non-existent member causes an exception and no modification to the list. 
	 * Access: domain admin sufficient.
	 * 
	 * @param  string $id  Zimbra ID
	 * @param  array  $dlm Members.
	 * @return mix
	 */
	function removeDistributionListMember($id, array $dlm);

	/**
	 * Rename Account.
	 * Access: domain admin sufficient.
	 * Note: this request is by default proxied to the account's home server. 
	 * 
	 * @param  string $id      Zimbra ID
	 * @param  array  $newName New account name.
	 * @return mix
	 */
	function renameAccount($id, $newName);

	/**
	 * Rename Calendar Resource.
	 * Access: domain admin sufficient.
	 * Note: this request is by default proxied to the account's home server. 
	 * 
	 * @param  string $id      Zimbra ID
	 * @param  array  $newName New Calendar Resource name.
	 * @return mix
	 */
	function renameCalendarResource($id, $newName);

	/**
	 * Rename Class of Service (COS).
	 * 
	 * @param  string $id      Zimbra ID
	 * @param  array  $newName New COS name.
	 * @return mix
	 */
	function renameCos($id, $newName);

	/**
	 * Rename Distribution List.
	 * Access: domain admin sufficient.
	 * 
	 * @param  string $id      Zimbra ID
	 * @param  array  $newName New Distribution List name.
	 * @return mix
	 */
	function renameDistributionList($id, $newName);

	/**
	 * Rename LDAP Entry.
	 * 
	 * @param  string $dn    A valid LDAP DN String (RFC 2253) that identifies the LDAP object
	 * @param  array  $newDn New DN - a valid LDAP DN String (RFC 2253) that describes the new DN to be given to the LDAP object.
	 * @return mix
	 */
	function renameLDAPEntry($dn, $newDn);

	/**
	 * Rename Unified Communication Service.
	 * 
	 * @param  string $id      Zimbra ID
	 * @param  array  $newName New UC Service name.
	 * @return mix
	 */
	function renameUCService($id, $newName);

	/**
	 * Removes all account loggers and reloads /opt/zimbra/conf/log4j.properties.
	 * 
	 * @return mix
	 */
	function resetAllLoggers();

	/**
	 * Perform an action related to a Restore from backup.
	 *   1. When includeIncrementals is 1 (true), any incremental backups
	 *      from the last full backup are also restored. Default to 1 (true).
	 *   2. When sysData is 1 (true), restore system tables and local config.
	 *   3. If label is not specified, restore from the latest full backup.
	 *   4. Prefix is used to produce new account names if the name is reused
	 *      or a new account is to be created
	 * Network edition only API.
	 * 
	 * @param  string $restore Restore specification.
	 * @param  string $file    File copier specification.
	 * @param  string $accounts Account selector - either one <account name="all"/> or a list of <account name="{account email addr}"/>
	 * @return mix
	 */
	function restore(array $restore, array $file = array(), array $accounts = array('all'));

	/**
	 * Resume sync with a device or all devices attached to an account if currently suspended.
	 * This will cause a policy reset, but will not reset sync data.
	 * 
	 * @param  string $account The name used to identify the account.
	 * @param  string $device  Device ID.
	 * @return mix
	 */
	function resumeDevice($account, $device = '');

	/**
	 * Revoke a right from a target that was previously granted to an individual or group grantee.
	 * 
	 * @param  string $target  The name used to identify the target.
	 * @param  string $type    Target type. Valid values (account|calresource|cos|dl|group|domain|server|ucservice|xmppcomponent|zimlet|config|global).
	 * @param  array  $grantee Grantee selector.
	 * @param  array  $right   Right selector.
	 * @return mix
	 */
	function revokeRight($target, $type, array $grantee, array $right);

	/**
	 * Rollover Redo Log.
	 * Network edition only API.
	 * 
	 * @return mix
	 */
	function rolloverRedoLog();

	/**
	 * Runs the server-side unit test suite.
	 * If <test>'s are specified, then run the requested tests (instead of the standard test suite).
	 * Otherwise the standard test suite is run.
	 * 
	 * @param  string $tests Array test name.
	 * @return mix
	 */
	function runUnitTests(array $tests = array());

	/**
	 * Schedule backups.
	 * Network edition only API.
	 * 
	 * @param  string $server Server name.
	 * @return mix
	 */
	function scheduleBackups($server);

	/**
	 * Search Accounts.
	 * Access: domain admin sufficient (a domain admin can't specify "domains" as a type).
	 * 
	 * @param  string  $query  Query string - should be an LDAP-style filter string (RFC 2254).
	 * @param  string  $domain The domain name to limit the search to.
	 * @param  bool    $apply  Flag whether or not to apply the COS policy to account. Specify 0 (false) if only requesting attrs that aren't inherited from COS.
	 * @param  array   $types  Array of types to return. Legal values are: accounts|resources (default is accounts).
	 * @param  array   $attrs  Array of attrs to return ("displayName", "zimbraId", "zimbraAccountStatus").
	 * @param  string  $sort   Name of attribute to sort on. Default is the account name.
	 * @param  bool    $asc    Whether to sort in ascending order. Default is 1 (true).
	 * @param  integer $limit  The maximum number of accounts to return (0 is default and means all).
	 * @param  integer $offset The starting offset (0, 25, etc).
	 * @return mix
	 */
	function searchAccounts($query, $domain = '', $apply = TRUE, array $types = array('accounts'), array $attrs = array(), $sortBy = '', $asc = TRUE, $limit = 0, $offset = 0);

	/**
	 * Search Auto Prov Directory.
	 * Only one of <name> or <query> can be provided.
	 * If neither is provided, the configured search filter for auto provision will be used.
	 * 
	 * @param  string  $key     Name of attribute for the key.
	 * @param  string  $domain  Domain name to limit the search to (do not use if searching for domains).
	 * @param  string  $query   Query string - should be an LDAP-style filter string (RFC 2254).
	 * @param  string  $name    Name to fill the auto provisioning search template configured on the domain.
	 * @param  integer $max     Maximum results that the backend will attempt to fetch from the directory before returning an account.TOO_MANY_SEARCH_RESULTS error.
	 * @param  bool    $refresh Refresh - whether to always re-search in LDAP even when cached entries are available. 0 (false) is the default.
	 * @param  array   $attrs   Array of attributes.
	 * @param  integer $limit   The number of accounts to return per page (0 is default and means all).
	 * @param  integer $offset  The starting offset (0, 25, etc).
	 * @return mix
	 */
	function searchAutoProvDirectory($key, $domain, $query = '', $name = '', $max = 0, $refresh = FALSE, array $attrs = array(), $limit = 0, $offset = 0);

	/**
	 * Search for Calendar Resources.
	 * Access: domain admin sufficient.
	 * 
	 * @param  string  $search Search filter condition.
	 * @param  string  $domain The domain name to limit the search to.
	 * @param  bool    $apply  Flag whether or not to apply the COS policy to calendar resource. Specify 0 (false) if only requesting attrs that aren't inherited from COS.
	 * @param  array   $attrs  Array of attributes.
	 * @param  string  $sort   Name of attribute to sort on. default is the calendar resource name.
	 * @param  bool    $asc    Whether to sort in ascending order. Default is 1 (true).
	 * @param  integer $limit  The maximum number of calendar resources to return (0 is default and means all).
	 * @param  integer $offset The starting offset (0, 25, etc).
	 * @return mix
	 */
	function searchCalendarResources(array $conds = array(), $domain = '', $apply = TRUE, array $attrs = array(), $sort = '', $asc = TRUE, $limit = 0, $offset = 0);

	/**
	 * Search directory.
	 * Access: domain admin sufficient (though a domain admin can't specify "domains" as a type).
	 * 
	 * @param  string  $query       Query string - should be an LDAP-style filter string (RFC 2254).
	 * @param  string  $domain      The domain name to limit the search to.
	 * @param  bool    $applyCos    Flag whether or not to apply the COS policy to account. Specify 0 (false) if only requesting attrs that aren't inherited from COS.
	 * @param  bool    $applyConfig Whether or not to apply the global config attrs to account. specify 0 (false) if only requesting attrs that aren't inherited from global config.
	 * @param  bool    $countOnly   Whether response should be count only. Default is 0 (false).
	 * @param  array   $attrs       Array of attributes.
	 * @param  array   $types       Array of types to return. Legal values are: accounts|distributionlists|aliases|resources|domains|coses. (default is accounts)
	 * @param  integer $max         Maximum results that the backend will attempt to fetch from the directory before returning an account.TOO_MANY_SEARCH_RESULTS error.
	 * @param  string  $sort        Name of attribute to sort on. Default is the account name.
	 * @param  bool    $asc         Whether to sort in ascending order. Default is 1 (true).
	 * @param  integer $limit       The maximum number of accounts to return (0 is default and means all).
	 * @param  integer $offset      The starting offset (0, 25, etc).
	 * @return mix
	 */
	function searchDirectory($query = '', $domain = '', $applyCos = TRUE, $applyConfig = TRUE, $countOnly = FALSE, array $attrs = array(), array $types = array('accounts'), $max = 0, $sort = '', $asc = TRUE, $limit = 0, $offset = 0);

	/**
	 * Search Global Address Book (GAL).
	 * Notes: admin verison of mail equiv. Used for testing via zmprov.
	 * 
	 * @param  string  $domain    Domain name.
	 * @param  string  $type      Type of addresses to search. Valid values: all|account|resource|group.
	 * @param  string  $name      Name.
	 * @param  string  $galAcctId GAL account ID.
	 * @param  integer $limit     The maximum number of entries to return (0 is default and means all).
	 * @return mix
	 */
	function searchGal($domain, $type = 'account', $name = '', $galAcctId = '', $limit = 0);

	/**
	 * SearchMultiMailboxRequest is a version of the standard <SearchRequest> API
	 * that allows the caller to specify one or more <mbx> targets to be searched in.
	 * Most parameters are identical to a normal search.
	 * Some search parameters will behave strangely when use across multiple mailboxes.
	 * In particular, Folders and Tags are local to the account making
	 * the search (NOT the account being searched in) -- so things like "in:inbox" will
	 * not return extra results even if multiple mailboxes are specified.
	 * In general this API is intended to be used for text-based searches,
	 * or searches with system flags such as "unread".
	 * Network edition only API.
	 * 
	 * @param  array  $options Search specification options.
	 * @param  array  $header  Search header.
	 * @param  array  $tz      Timezone specification.
	 * @param  array  $cursor  Cursor specification.
	 * @param  array  $mbx     Mailbox specification.
	 * @param  string $locale  Client locale identification.
	 * @return mix
	 */
	//function searchMultiMailbox(array $options = array(), array $header = array(), array $tz = array(), array $cursor = array(), array $mbx = array(), $locale = '');

	/**
	 * Set current volume.
	 * Notes: Each SetCurrentVolumeRequest can set only one current volume type.
	 * 
	 * @param  integer $id   ID.
	 * @param  integer $type Volume type: 1 (primary message), 2 (secondary message) or 10 (index).
	 * @return mix
	 */
	function setCurrentVolume($id, $type);

	/**
	 * Set Password.
	 * Access: domain admin sufficient.
	 * Note: this request is by default proxied to the account's home server.
	 * 
	 * @param  string $id       Zimbra ID.
	 * @param  string $password New password.
	 * @return mix
	 */
	function setPassword($id, $password);

	/**
	 * Suspend a device or all devices attached to an account from further sync actions.
	 * 
	 * @param  string $account The name used to identify the account.
	 * @param  string $device  Device ID.
	 * @return mix
	 */
	function suspendDevice($account, $device = '');

	/**
	 * Sync GalAccount.
	 * Notes:
	 *   1. If fullSync is set to false (or unset) the default behavior
	 *      is trickle sync which will pull in any new contacts or modified contacts since last sync.
	 *   2. If fullSync is set to true, then the server will go through all the contacts
	 *      that appear in GAL, and resolve deleted contacts in addition to new or modified ones.
	 *   3. If reset attribute is set, then all the contacts will be populated again,
	 *      regardless of the status since last sync.
	 *      Reset needs to be done when there is a significant change in the configuration,
	 *      such as filter, attribute map, or search base.
	 * 
	 * @param  string $id         Account ID.
	 * @param  array  $galAccounts SyncGalAccount data source specifications.
	 * @return mix
	 */
	function syncGalAccount(array $galAccounts = array());

	/**
	 * Undeploy Zimlet.
	 * 
	 * @param  string $name   Zimlet name.
	 * @param  string $action Action.
	 * @return mix
	 */
	function undeployZimlet($name, $action = '');

	/**
	 * Forces the mailbox of the specified account to get unloaded from memory.
	 * Network edition only API
	 * 
	 * @param  string $account Account email address.
	 * @return mix
	 */
	function unloadMailbox($account);

	/**
	 * This request is invoked by move destination server against move source server
	 * to indicate the completion of mailbox move.
	 * This request is also invoked to reset the state after a mailbox move that died unexpectedly,
	 * such as when the destination server crashed.
	 * Network edition only API
	 * 
	 * @param  string $account Account email address.
	 * @param  string $dest    Hostname of destination server.
	 * @return mix
	 */
	function unregisterMailboxMoveOut($account, $dest);

	/**
	 * Update device status.
	 * 
	 * @param  string $account The name used to identify the account.
	 * @param  string $device  Device ID.
	 * @param  string $status  Device status - enabled|disabled|locked|wiped.
	 * @return mix
	 */
	function updateDeviceStatus($account, $device, $status = 'enabled');

	/**
	 * Generate a new Cisco Presence server session ID and persist the newly generated session id
	 * in zimbraUCCiscoPresenceSessionId attribute for the specified UC service..
	 * 
	 * @param  string $ucservice The UC service name.
	 * @param  string $username  App username.
	 * @param  string $password  App password.
	 * @param  array  $attrs     Attributes.
	 * @return mix
	 */
	function updatePresenceSessionId($ucservice, $username, $password, array $attrs = array());

	/**
	 * Upload domain certificate.
	 * 
	 * @param  string $certAid      Certificate attach ID.
	 * @param  string $certFilename Certificate name.
	 * @param  string $keyAid       Key attach ID.
	 * @param  string $keyFilename  Key name.
	 * @return mix
	 */
	function uploadDomCert($certAid, $certFilename, $keyAid, $keyFilename);

	/**
	 * Upload proxy CA.
	 * 
	 * @param  string $aid      Certificate attach ID.
	 * @param  string $filename Certificate name.
	 * @return mix
	 */
	function uploadProxyCA($aid, $filename);

	/**
	 * Verify Certificate Key.
	 * 
	 * @param  string $cert    Certificate.
	 * @param  string $privkey Private key.
	 * @return mix
	 */
	function verifyCertKey($cert = '', $privkey = '');

	/**
	 * Verify index.
	 * 
	 * @param  string $id Account ID.
	 * @return mix
	 */
	function verifyIndex($id);

	/**
	 * Verify Store Manager.
	 * 
	 * @param  integer $fileSize.
	 * @param  integer $num.
	 * @param  bool    $checkBlobs.
	 * @return mix
	 */
	function verifyStoreManager($fileSize = 0, $num = 0, $checkBlobs = 0);

	/**
	 * Version Check.
	 * 
	 * @param  integer $action Action. Either check or status.
	 * @return mix
	 */
	function versionCheck($action = 'check');
}

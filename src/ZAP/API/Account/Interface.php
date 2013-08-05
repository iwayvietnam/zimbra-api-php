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
 * ZAP_API_Account_Interface is a interface which allows to connect Zimbra API account functions via SOAP
 * @package   ZAP
 * @category  Account
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
interface ZAP_API_Account_Interface
{
	/**
	 * Authenticate for an account
	 *
	 * @param  string $account  The user account.
	 * @param  string $password The user password.
	 * @return authentication token
	 */
	function auth($account, $password);

	/**
	 * Authenticate for an account by token
	 *
	 * @param  string   $account The user account.
	 * @param  string   $token   The authentication token.
	 * @return authentication token
	 */
	function authByToken($account, $token);

	/**
	 * Authenticate for an account by token
	 *
	 * @param  string $account The user account.
	 * @param  string $key     Pre authentication key
	 * @return authentication token
	 */
	function preAuth($account, $key);

	/**
	 * End the current session, removing it from all caches.
	 * Called when the browser app (or other session-using app) shuts down.
	 * Has no effect if called in a <nosession> context.
	 *
	 * @return mixed
	 */
	function logout();

	/**
	 * Change password
	 *
	 * @param  string $oldPassword Old password
	 * @param  string $password    New Password to assign
	 * @param  string $virtualHost Virtual-host is used to determine the domain of the account name
	 * @return mixed
	 */
	function changePassword($oldPassword, $password, $virtualHost = '');

	/**
	 * Perform an autocomplete for a name against the Global Address List
	 *
	 * @param  string $name The name to test for autocompletion
	 * @return mixed
	 */
	function autoCompleteGal($name);

	/**
	 * Checks whether this account (auth token account or requested account id) is allowed access to the specified feature.
	 * Network edition only API.
	 * These are the valid values (which are case-insensitive):
	 * 1. MAPI  - Zimbra Connector For Outlook
	 * 2. iSync - Apple iSync
	 * 3. SMIME - Zimbra SMIME
	 * 4. BES   - Zimbra Connector for BlackBerry Enterprise Server
	 *
	 * @param  string $feature The licensable feature
	 * @return mixed
	 */
	function checkLicense($feature);

	/**
	 * Check if the authed user has the specified right(s) on a target.
	 * Target type: account|calresource|cos|dl|group|domain|server|ucservice|xmppcomponent|zimlet|config|global
	 *
	 * @param  string $type   Target type.
	 * @param  string $key    Key for target.
	 * @param  array  $rights Rights
	 * @return mixed
	 */
	function checkRights($type, $key, array $rights);

	/**
	 * Create a Distribution List
	 * Note: authed account must have the privilege to create dist lists in the domain
	 *
	 * @param  string $name  Name for the new Distribution List
	 * @param  array  $attrs Attributes specified as key value pairs
	 * @return mixed
	 */
	function createDistributionList($name, array $attrs = array());

	/**
	 * Create an Identity
	 *
	 * @param  string $name  Identity name
	 * @param  array  $attrs Attributes
	 * @return mixed
	 */
	function createIdentity($name, array $attrs = array());

	/**
	 * Create a signature
	 *
	 * @param  string $name    Identity name
	 * @param  string $content Content of the signature
	 * @param  string $type    Content type of the signature
	 * @return mixed
	 */
	function createSignature($name, $content = '', $type = 'text/plain');

	/**
	 * Delete an Identity
	 *
	 * @param  string $name Identity name
	 * @return mixed
	 */
	function deleteIdentity($name);

	/**
	 * Delete a signature
	 *
	 * @param  string $name Identity name
	 * @return mixed
	 */
	function deleteSignature($name);

	/**
	 * Return all targets of the specified rights applicable to the requested account
	 *
	 * @param  array $rights The rights.
	 * @return mixed
	 */
	function discoverRights(array $rights);

	/**
	 * Perform an action on a Distribution List 
	 * Notes:
	 *   1. Authorized account must be one of the list owners
	 *   2. For owners/rights, only grants on the group itself will be modified,
	 *      grants on domain and globalgrant (from which the right can be inherited) will not be touched.
	 *      Only admins can modify grants on domains and globalgrant,
	 *      owners of groups can only modify grants on the group entry.
	 *
	 * @param  string $name   Identifies the distribution list to act upon
	 * @param  array  $action Specifies the action to perform
	 * @param  array  $attrs  Attributes
	 * @return mixed
	 */
	function distributionListAction($name, array $action, array $attrs = array());

	/**
	 * End the current session, removing it from all caches.
	 * Called when the browser app (or other session-using app) shuts down.
	 * Has no effect if called in a <nosession> context.
	 *
	 * @return mixed
	 */
	function endSession();

	/**
	 * Returns groups the user is either a member or an owner of.
	 * Notes:
	 *   1. isOwner is returned only if ownerOf on the request is 1 (true).
	 *   2. For owners/rights, only grants on the group itself will be modified,
	 * For example, if isOwner="1" and isMember="none" on the request,
	 * and user is an owner and a member of a group,
	 * the returned entry for the group will have isOwner="1",
	 * but isMember will not be present.
	 *
	 * @param  bool   $ownerOf   ownerOf. Set to 1 if the response should include groups the user is an owner of. Set to 0 (default) if do not need to know which groups the user is an owner of.
	 * @param  string $memberOf memberOf. Possible values: all - need all groups the user is a direct or indirect member of. none - do not need groups the user is a member of. directOnly (default) - need groups the account is a direct member of.
	 * @param  array  $attrs  Attributes
	 * @return mixed
	 */
	function getAccountDistributionLists($ownerOf = FALSE, $memberOf = 'directOnly', array $attrs = array());

	/**
	 * Get Information about an account
	 *
	 * @param  string $account Use to identify the account
	 * @return mixed
	 */
	function getAccountInfo($account = NULL);

	/**
	 * Returns all locales defined in the system
	 *
	 * @return mixed
	 */
	function getAllLocales();

	/**
	 * Returns the known CSV formats that can be used for import and export of addressbook.
	 *
	 * @return mixed
	 */
	function getAvailableCsvFormats();

	/**
	 * Get the intersection of all translated locales installed on the server
	 * and the list specified in zimbraAvailableLocale
	 * The locale list in the response is sorted by display name (name attribute).
	 *
	 * @return mixed
	 */
	function getAvailableLocales();

	/**
	 * Get the intersection of installed skins on the server and the list specified
	 * in the zimbraAvailableSkin on an account (or its CoS).
	 * If none is set in zimbraAvailableSkin, get the entire list of installed skins.
	 * The installed skin list is obtained by a directory scan of the designated location
	 * of skins on a server.
	 *
	 * @return mixed
	 */
	function getAvailableSkins();

	/**
	 * Get a distribution list, optionally with ownership information an granted rights.
	 *
	 * @param  string $name       Name of the distribution list
	 * @param  array  $attrs      Attributes of the distribution list
	 * @param  bool   $needOwners Whether to return owners, default is 0 (i.e. Don't return owners)
	 * @param  string $needRights Return grants for the specified (comma-seperated) rights. 
	 * @return mixed
	 */
	function getDistributionList($name, array $attrs = array(), $needOwners = FALSE, $needRights = '');

	/**
	 * Get the list of members of a distribution list.
	 *
	 * @param  string $name Name of the distribution list
	 * @param  integer $limit  The number of members to return (0 is default and means all).
	 * @param  integer $offset The starting offset (0, 25 etc).
	 * @return mixed
	 */
	function getDistributionListMembers($name, $limit = 0, $offset = 0);

	/**
	 * Get the identities for the authed account.
	 *
	 * @return mixed
	 */
	function getIdentities();

	/**
	 * Get information about an account by sections.
	 * Sections are: mbox,prefs,attrs,zimlets,props,idents,sigs,dsrcs,children
	 *
	 * @param  array $sections Array of sections to return information about. 
	 * @param  array $rights   Array of rights to return information about.
	 * @return mixed
	 */
	function getInfo(array $sections = array(), array $rights = array());

	/**
	 * Get preferences for the authenticated account 
	 *
	 * @param  array $prefs Array of preferences. 
	 * @return mixed
	 */
	function getPrefs(array $prefs = array());

	/**
	 * Get account level rights. 
	 *
	 * @param  array $ace Specify Access Control Entries. 
	 * @return mixed
	 */
	function getRights(array $ace = array());

	/**
	 * Get SMIME Public Certificates Stores specified in <store> will be attempted
	 * in the order they appear in the comma separated list.
	 * Network edition only API.
	 * Comma separated list of store types
	 * Valid store types:
	 * 1. CONTACT - contacts
	 * 2. GAL     - Global Address List (internal and external)
	 * 3. LDAP    - external LDAP (see GetSMIMEConfig and ModifySMIMEConfig)
	 *
	 * @param  array $stores Information on public certificate stores
	 * @param  array $emails Array of email addresses
	 * @return mixed
	 */
	function getSMIMEPublicCerts(array $stores, array $emails = array());

	/**
	 * Get information about published shares
	 *
	 * @param  string $owner       Specifies the owner of the share
	 * @param  string $grantee     Filter by the specified grantee type
	 * @param  bool   $internal    Flags that have been proxied to this server because the specified "owner account" is homed here. Do not proxy in this case. (Used internally by ZCS)
	 * @param  bool   $includeSelf Flag whether own shares should be included. 0 if shares owned by the requested account should not be included in the response. 1 (default) include shares owned by the requested account
	 * @return mixed
	 */
	function getShareInfo($owner = '', array $grantee = array(), $internal = TRUE, $includeSelf = TRUE);

	/**
	 * Get Signatures associated with an account
	 *
	 * @return mixed
	 */
	function getSignatures();

	/**
	 * Get Signatures associated with an account
	 * Note: This request will return a SOAP fault if the zimbraSoapExposeVersion
	 *       server/globalconfig attribute is set to FALSE.
	 *
	 * @return mixed
	 */
	function getVersionInfo();

	/**
	 * Get the anti-spam WhiteList and BlackList addresses
	 *
	 * @return  mixed
	 */
	function getWhiteBlackList();

	/**
	 * Grant account level rights
	 *
	 * @param  array $ace Specify Access Control Entries
	 * @return mixed
	 */
	function grantRights(array $ace);

	/**
	 * Modify an Identity
	 *
	 * @param  string $name  Identity name
	 * @param  array  $attrs Attributes
	 * @return mixed
	 */
	function modifyIdentity($name, array $attrs = array());

	/**
	 * Modify preferences
	 *
	 * @param  array $prefs Specify the preferences to be modified
	 * @return mixed
	 */
	function modifyPrefs(array $prefs = array());

	/**
	 * Modify properties related to zimlets
	 *
	 * @param  string $name      Zimlet name
	 * @param  array  $prop_name Property name
	 * @param  array  $value     Property value
	 * @return mixed
	 */
	function modifyProperties($name, $prop_name, $value);

	/**
	 * Change attributes of the given signature
	 * Only the attributes specified in the request are modified.
	 * Note: The Server identifies the signature by id,
	 *       if the name attribute is present and is different from the current name of the signature,
	 *       the signature will be renamed.
	 *
	 * @param  string $name    Identity name
	 * @param  string $content Content of the signature
	 * @param  string $type    Content type of the signature
	 * @return mixed
	 */
	function modifySignature($name, $content = '', $type = 'text/plain');

	/**
	 * Modify the anti-spam WhiteList and BlackList addresses
	 * Note: If no <addr> is present in a list, it means to remove all addresses in the list. 
	 *
	 * @param  array $whiteList Identity name
	 * @param  array $blackList Content of the signature
	 * @return mixed
	 */
	function modifyWhiteBlackList(array $whiteList, array $blackList = array());

	/**
	 * Modify Zimlet Preferences
	 *
	 * @param  string $name     Zimlet name
	 * @param  bool   $presence Zimlet presence setting
	 * @return mixed
	 */
	function modifyZimletPrefs($name, $presence = TRUE);

	/**
	 * Revoke account level rights
	 *
	 * @param  array $ace Specify Access Control Entries
	 * @return mixed
	 */
	function revokeRights(array $ace = array());

	/**
	 * Search Global Address List (GAL) for calendar resources
	 * "attrs" attribute - comma-separated list of attrs to
	 * return ("displayName", "zimbraId", "zimbraCalResType")
	 *
	 * @param  array  $conds   Search filter conditions
	 * @param  array  $cursor  Cursor specification
	 * @param  array  $options Search options
	 * @param  string $name    If specified, passed through to the GAL search as the search key
	 * @param  string $locale  Client locale identification.
	 * @return mixed
	 */
	function searchCalendarResources(array $conds, array $cursor = array(), array $options = array(), $name = '', $locale = '');

	/**
	 * Search Global Address List (GAL)
	 *
	 * @param  array  $conds   Search filter conditions.
	 * @param  array  $cursor  Cursor specification.
	 * @param  array  $options Search options.
	 * @param  string $locale  Client locale identification.
	 * @return mixed
	 */
	function searchGal(array $conds = array(), array $cursor = array(), array $options = array(), $locale = '');

	/**
	 * Subscribe to a distribution list
	 *
	 * @param  string $name      Distribution list name
	 * @param  bool   $subscribe Subscribe
	 * @return mixed
	 */
	function subscribeDistributionList($name, $subscribe = TRUE);

	/**
	 * Synchronize with the Global Address List
	 *
	 * @param  string $token     The previous synchronization token if applicable
	 * @param  string $galAcctId GAL sync account ID
	 * @return mixed
	 */
	function syncGal($token, $galAcctId = '');
}
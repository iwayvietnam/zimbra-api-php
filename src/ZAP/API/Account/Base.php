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
 * ZAP_API_Account_Base is a abstract class which allows to connect Zimbra API account functions via SOAP
 * @package   ZAP
 * @category  Account
 * @author    Nguyen Van Nguyen - nguyennv1981@gmail.com
 * @copyright Copyright © 2013 by iWay Vietnam. (http://www.iwayvietnam.com)
 */
abstract class ZAP_API_Account_Base extends ZAP_API_Account
{
	/**
	 * @var array The account identity allowed attributes.
	 */
	private $_allowedAttrs = array(
		'zimbraPrefBccAddress',
		'zimbraPrefForwardIncludeOriginalText',
		'zimbraPrefForwardReplyFormat',
		'zimbraPrefForwardReplyPrefixChar',
		'zimbraPrefFromAddress',
		'zimbraPrefFromDisplay',
		'zimbraPrefMailSignature',
		'zimbraPrefMailSignatureEnabled',
		'zimbraPrefMailSignatureStyle',
		'zimbraPrefReplyIncludeOriginalText',
		'zimbraPrefReplyToAddress',
		'zimbraPrefReplyToDisplay',
		'zimbraPrefReplyToEnabled',
		'zimbraPrefSaveToSent',
		'zimbraPrefSentMailFolder',
		'zimbraPrefUseDefaultIdentitySettings',
		'zimbraPrefWhenInFolderIds',
		'zimbraPrefWhenInFoldersEnabled',
		'zimbraPrefWhenSentToAddresses',
		'zimbraPrefWhenSentToEnabled',
	);
	/**
	 * ZAP_Account_Base constructor
	 *
	 * @param  string $server The server name.
	 * @param  string $port   The server port.
	 * @param  bool   $ssl    Ssl.
	 */
	public function __construct($server, $port = 443, $ssl = TRUE)
	{
		$this->_server = $server;
		$this->_port = (int) $port;
		$this->_location = (((bool) $ssl) ? 'https' : 'http').'://'.$server.':'.$port.$this->_path;
		$this->_namespace = 'urn:zimbraAccount';
	}

	/**
	 * Authenticate for an account
	 *
	 * @param  string $account  The user account.
	 * @param  string $password The user password.
	 * @return authentication token
	 */
	public function auth($account, $password)
	{
		$this->_account = $account;
		$params = array(
			'account' => $account,
			'password' => $password,
		);

		$result = $this->_client->soapRequest('AuthRequest', $params);
		$authToken = $result->AuthResponse->authToken;
		if($authToken) $this->_client->authToken($authToken);
		return $result->AuthResponse;
	}

	/**
	 * Authenticate for an account by token
	 *
	 * @param  string $account The user account.
	 * @param  string $token   The authentication token.
	 * @return authentication token
	 */
	public function authByToken($account, $token)
	{
		$this->_account = $account;
		$params = array(
			'account' => $account,
			'authToken' => array(
				'verifyAccount' => '1',
				'_' => $token,
			),
		);

		$result = $this->_client->soapRequest('AuthRequest', $params);
		$authToken = $result->AuthResponse->authToken;
		if($authToken) $this->_client->authToken($authToken);
		return $result->AuthResponse;
	}

	/**
	 * Authenticate for an account with preAuth key.
	 *
	 * @param  string $account The user account.
	 * @param  string $key     Pre authentication key
	 * @return authentication token
	 */
	public function preAuth($account, $key)
	{
		$this->_account = $account;
		$now = time() * 1000;
		$expire = 0;
		$preauth_string = $account . "|name|" . $expire . "|" . $now;
		$preauth = hash_hmac("sha1", $preauth_string, $key);
		$params = array(
			'account' => array(
				'_' => $account,
				'by' => 'name',
			),
			'preauth' => array(
				'_' => $preauth,
				'timestamp' => $now,
				'expires' => '0',
			),
		);
		$result = $this->_client->soapRequest('AuthRequest', $params);
		$authToken = $result->AuthResponse->authToken;
		if($authToken) $this->_client->authToken($authToken);
		return $result->AuthResponse;
	}

	/**
	 * End the current session, removing it from all caches.
	 * Called when the browser app (or other session-using app) shuts down.
	 * Has no effect if called in a <nosession> context.
	 *
	 * @return mixed
	 */
	public function logout()
	{
		return $this->endSession();
	}

	/**
	 * Change password
	 *
	 * @param  string $oldPassword Old password
	 * @param  string $password    New Password to assign
	 * @param  string $virtualHost Virtual-host is used to determine the domain of the account name
	 * @return mixed
	 */
	public function changePassword($oldPassword, $password, $virtualHost = '')
	{
		$params = array(
			'account' => $this->_account,
			'oldPassword' => $oldPassword,
			'password' => $password,
		);
		if(!empty($virtualHost))
		{
			$params['virtualHost'] = $virtualHost;
		}
		$result = $this->_client->soapRequest('ChangePasswordRequest', $params);
		$authToken = $result->ChangePasswordResponse->authToken;
		return $result->ChangePasswordResponse;
	}

	/**
	 * Perform an autocomplete for a name against the Global Address List
	 *
	 * @param  string $name The name to test for autocompletion
	 * @return mixed
	 */
	public function autoCompleteGal($name){
		$options = array
		(
			'name' => $name,
			'type' => 'all',
		);
		$result = $this->_client->soapRequest('AutoCompleteGalRequest', array(), $options);
		return $result->AutoCompleteGalResponse;
	}

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
	public function checkLicense($feature)
	{
		$options = array(
			'feature' => $feature,
		);
		$result = $this->_client->soapRequest('CheckLicenseRequest', array(), $options);
		return $result->CheckLicenseResponse;		
	}

	/**
	 * Check if the authed user has the specified right(s) on a target.
	 * Type:account|calresource|cos|dl|group|domain|server|ucservice|xmppcomponent|zimlet|config|global
	 *
	 * @param  string $type   Target type.
	 * @param  string $key    Key for target.
	 * @param  array  $rights Rights
	 * @return mixed
	 */
	public function checkRights($type, $key, array $rights)
	{
		$params = array(
			'target' => array(
				'type' => $type,
				'by' => 'name',
				'key' => $key,
				'right' => array(),
			),
		);
		foreach ($rights as $right)
		{
			$params['target']['right'][] = $right;
		}
		$result = $this->_client->soapRequest('CheckRightsRequest', $params);
		return $result->CheckRightsResponse;
	}

	/**
	 * Create a Distribution List
	 * Note: authed account must have the privilege to create dist lists in the domain
	 *
	 * @param  string $name  Name for the new Distribution List
	 * @param  array  $attrs Attributes specified as key value pairs
	 * @return mixed
	 */
	public function createDistributionList($name, array $attrs = array()){
		$params = array();
		$validAttrs = array('description', 'zimbraNotes');
		$attributes = $this->_attributes($attrs, 'n', $validAttrs);
		if(count($attributes))
		{
			$params['a'] = $attributes;
		}
		$result = $this->_client->soapRequest('CreateDistributionListRequest', $params, array('name' => $name));
		return $result->CreateDistributionListResponse;
	}

	/**
	 * Create an Identity
	 *
	 * @param  string $name  Identity name
	 * @param  array  $attrs Attributes
	 * @return mixed
	 */
	public function createIdentity($name, array $attrs = array()){
		$params = array(
			'identity' => array(
				'name' => $name,
			),
		);
		$attributes = $this->_attributes($attrs, 'n', $this->_allowedAttrs);
		if(count($attributes))
		{
			$params['identity']['a'] = $attributes;			
		}
		$result = $this->_client->soapRequest('CreateIdentityRequest', $params);
		return $result->CreateIdentityResponse;
	}

	/**
	 * Create a signature
	 *
	 * @param  string $name    Identity name
	 * @param  string $content Content of the signature
	 * @param  string $type    Content type of the signature
	 * @return mixed
	 */
	public function createSignature($name, $content = '', $type = 'text/plain')
	{
		$params = array(
			'signature' => array(
				'name' => $name,
				'content' => array(
					'type' => $type,
					'_' => $content,
				),
			),
		);
		$result = $this->_client->soapRequest('CreateSignatureRequest', $params);
		return $result->CreateSignatureResponse;
	}

	/**
	 * Delete an Identity
	 *
	 * @param  string $name Identity name
	 * @return mixed
	 */
	public function deleteIdentity($name)
	{
		$params = array(
			'identity' => array(
				'name' => $name,
			),
		);
		$result = $this->_client->soapRequest('DeleteIdentityRequest', $params);
		return $result->DeleteIdentityResponse;
	}

	/**
	 * Delete a signature
	 *
	 * @param  string $name Identity name
	 * @return mixed
	 */
	public function deleteSignature($name)
	{
		$params = array(
			'signature' => array(
				'name' => $name,
			),
		);
		$result = $this->_client->soapRequest('DeleteSignatureRequest', $params);
		return $result->DeleteSignatureResponse;
	}

	/**
	 * Return all targets of the specified rights applicable to the requested account
	 *
	 * @param  array $rights The rights.
	 * @return mixed
	 */
	public function discoverRights(array $rights)
	{
		$params['right'] = array();
		foreach ($rights as $right)
		{
			$params['right'][] = $right;
		}
		$result = $this->_client->soapRequest('DiscoverRightsRequest', $params);
		return $result->DiscoverRightsResponse;
	}

	/**
	 * Perform an action on a Distribution List 
	 * Notes:
	 * 1. Authorized account must be one of the list owners
	 * 2. For owners/rights, only grants on the group itself will be modified, grants on domain and globalgrant (from which the right can be inherited) will not be touched.
	 *    Only admins can modify grants on domains and globalgrant, owners of groups can only modify grants on the group entry.
	 *
	 * @param  string $name   Identifies the distribution list to act upon
	 * @param  array  $action Specifies the action to perform
	 * @param  array  $attrs  Attributes
	 * @return mixed
	 */
	public function distributionListAction($name, array $action, array $attrs = array())
	{
		$params = array(
			'dl' => array(
				'by' => 'name',
				'_' => $name,
			),
			'action' => array(),
		);

		$arrOp = array('delete', 'modify', 'rename', 'addOwners', 'removeOwners', 'setOwners', 'grantRights', 'revokeRights', 'setRights', 'addMembers', 'removeMembers', 'acceptSubsReq', 'rejectSubsReq');
		$op = isset($action['op']) AND in_array($action['op'], $arrOp) ? $action['op'] : 'modify';
		$params['action']['op'] = $op;

		if($op === 'addMembers' OR $op === 'removeMembers')
		{
			$params['action']['dlm'] = array();
			if(isset($action['dlm']) AND is_array($action['dlm']))
			{
				foreach ($action['dlm'] as $dlm)
				{
					$params['action']['dlm'][] = $dlm;
				}
			}
		}
		if($op === 'rename')
		{
			$params['action']['newName'] = isset($action['newName']) ? $action['newName'] : 'newName';
		}

		if($op === 'addOwners' OR $op === 'removeOwners')
		{
			$params['action']['owner'] = $_setDlOwnerGrantee($action['owner']);
		}
		if($op === 'setOwners' AND isset($action['owner']))
		{
			$params['action']['owner'] = $_setDlOwnerGrantee($action['owner']);
		}

		if($op === 'grantRight' OR $op === 'revokeRight' OR $op === 'setRight')
		{
			$params['action']['right'] = array(
				'right' => $action['right']['right'],
			);
			if(isset($action['right']['grantee']))
			{
				$grantee = $_setDlOwnerGrantee($action['right']['grantee']);
				if(count($grantee))
				{
					$params['action']['right']['grantee'] = $grantee;
				}
			}
		}

		if($op === 'acceptSubsReq' OR $op === 'rejectSubsReq')
		{
			$subsReqOp = (isset($action['subsReq']['op']) AND in_array($action['subsReq']['op'], array('subscribe', 'unsubscribe'))) ? $action['subsReq']['op'] : 'subscribe';
			$params['action']['subsReq'] = array(
				'op' => $subsReqOp,
				'bccOwners' => isset($action['subsReq']['bccOwners']) ? 1: 0,
				'_' => isset($action['subsReq']['_']) ? $action['subsReq']['_']: '',				
			);
			if(isset($action['subsReq']))
			{
				$params['action']['subsReq'] = $action['subsReq'];			
			}
		}

		if(isset($action['a']) AND is_array($action['a']))
		{
			$attributes = $this->_attributes($action['a']);
			if(count($attributes))
			{
				$params['action']['a'] = $attributes;
			}
		}

		if(count($attrs))
		{
			$attributes = array();
			foreach ($attrs as $attr)
			{
				if(isset($attr['name']) AND !empty($attr['name']))
				{
					$arr = array();
					$arr['name'] = $attr['name'];
					if(isset($attr['pd']))
					{
						$arr['pd'] = $attr['pd'];
					}
					if(isset($attr['_']))
					{
						$arr['_'] = $attr['_'];
					}
					$attributes[] = $arr;
				}
			}
			if(count($attributes))
			{
				$params['a'] = $attributes;
			}
		}
		$result = $this->_client->soapRequest('DistributionListActionRequest', $params);
		return $result->DistributionListActionResponse;
	}

	/**
	 * End the current session, removing it from all caches.
	 * Called when the browser app (or other session-using app) shuts down.
	 * Has no effect if called in a <nosession> context.
	 *
	 * @return mixed
	 */
	public function endSession()
	{
		$result = $this->_client->soapRequest("EndSessionRequest");
		return $result->EndSessionResponse;
	}

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
	public function getAccountDistributionLists($ownerOf = FALSE, $memberOf = 'directOnly', array $attrs = array())
	{
		$options = array(
			'ownerOf' => (bool) $ownerOf ? 1 : 0,
			'memberOf' => in_array($memberOf, array('all', 'directOnly', 'none')) ? $memberOf : 'directOnly',
		);
		$attrsStr = $this->_commaAttributes($attrs);
		if(!empty($attrsStr))
		{
			$options['attrs'] = $attrsStr;
		}
		$result = $this->_client->soapRequest('GetAccountDistributionListsRequest', array(), $options);
		return $result->GetAccountDistributionListsResponse;
	}

	/**
	 * Get Information about an account
	 *
	 * @param  string $account Use to identify the account
	 * @return mixed
	 */
	public function getAccountInfo($account = NULL)
	{
		$account = empty($account) ? $this->_account : $account;
		$params = array(
			'account' => array(
				'by' => 'name',
				'_' => $account,
			),
		);
		$result = $this->_client->soapRequest('GetAccountInfoRequest', $params);
		return $result->GetAccountInfoResponse;
	}

	/**
	 * Returns all locales defined in the system
	 *
	 * @return mixed
	 */
	public function getAllLocales()
	{
		$result = $this->_client->soapRequest('GetAllLocalesRequest');
		return $result->GetAllLocalesResponse;
	}

	/**
	 * Returns the known CSV formats that can be used for import and export of addressbook.
	 *
	 * @return mixed
	 */
	function getAvailableCsvFormats()
	{		
		$result = $this->_client->soapRequest('GetAvailableCsvFormatsRequest');
		return $result->GetAvailableCsvFormatsResponse;
	}

	/**
	 * Get the intersection of all translated locales installed on the server and the list specified in zimbraAvailableLocale
	 * The locale list in the response is sorted by display name (name attribute).
	 *
	 * @return mixed
	 */
	public function getAvailableLocales()
	{
		$result = $this->_client->soapRequest('GetAvailableLocalesRequest');
		return $result->GetAvailableLocalesResponse;
	}

	/**
	 * Get the intersection of installed skins on the server and the list specified in the zimbraAvailableSkin on an account (or its CoS).
	 * If none is set in zimbraAvailableSkin, get the entire list of installed skins.
	 * The installed skin list is obtained by a directory scan of the designated location of skins on a server.
	 *
	 * @return mixed
	 */
	public function getAvailableSkins()
	{
		$result = $this->_client->soapRequest('GetAvailableSkinsRequest');
		return $result->GetAvailableSkinsResponse;
	}

	/**
	 * Get a distribution list, optionally with ownership information an granted rights.
	 *
	 * @param  string $name       Name of the distribution list
	 * @param  array  $attrs      Attributes of the distribution list
	 * @param  bool   $needOwners Whether to return owners, default is 0 (i.e. Don't return owners)
	 * @param  string $needRights Return grants for the specified (comma-seperated) rights. 
	 * @return mixed
	 */
	public function getDistributionList($name, array $attrs = array(), $needOwners = FALSE, $needRights = '')
	{
		$options = array(
			'needOwners' => (bool) $needOwners ? 1: 0,
		);
		if(!empty($needRights)) $options['$needRights'] = $needRights;
		$params = array(
			'dl' => array(
				'by' => 'name',
				'_' => $name,
			),
		);
		if(count($attrs))
		{
			$attributes = array();
			foreach ($attrs as $attr)
			{
				if(isset($attr['name']) AND !empty($attr['name']))
				{
					$arr = array();
					$arr['name'] = $attr['name'];
					if(isset($attr['pd']))
					{
						$arr['pd'] = $attr['pd'];
					}
					if(isset($attr['_']))
					{
						$arr['_'] = $attr['_'];
					}
					$attributes[] = $arr;
				}
			}
			if(count($attributes))
			{
				$params['a'] = $attributes;
			}
		}
		$result = $this->_client->soapRequest('GetDistributionListRequest', $params, $options);
		return $result->GetDistributionListResponse;
	}

	/**
	 * Get the list of members of a distribution list.
	 *
	 * @param  string $name Name of the distribution list
	 * @return mixed
	 */
	public function getDistributionListMembers($name, $limit = 0, $offset = 0)
	{
		$params = array(
			'dl' => array(
				'_' => $name,
			),
		);
		$options = array();
		if((int) $limit > 0)
		{
			$options['limit'] = (int) $limit;
			$options['offset'] = (int) $offset;
		}
		$result = $this->_client->soapRequest('GetDistributionListMembersRequest', $params, $options);
		return $result->GetDistributionListMembersResponse;
	}

	/**
	 * Get the identities for the authed account.
	 *
	 * @return mixed
	 */
	public function getIdentities()
	{
		$result = $this->_client->soapRequest('GetIdentitiesRequest');
		return $result->GetIdentitiesResponse;
	}

	/**
	 * Get information about an account by sections.
	 * Sections are: mbox,prefs,attrs,zimlets,props,idents,sigs,dsrcs,children
	 *
	 * @param  array $sections Array of sections to return information about. 
	 * @param  array $rights   Array of rights to return information about.
	 * @return mixed
	 */
	public function getInfo(array $sections = array(), array $rights = array())
	{
		$options = array();
		$validSections = array('mbox', 'prefs', 'attrs', 'zimlets', 'props', 'idents', 'sigs', 'dsrcs', 'children');
		$sectionsStr = $this->_commaAttributes($sections, $validSections);
		if(!empty($sectionsStr))
		{
			$options['sections'] = $sectionsStr;
		}

		$rightsStr = $this->_commaAttributes($rights);
		if(!empty($rightsStr))
		{
			$options['rights'] = $rightsStr;
		}

		$result = $this->_client->soapRequest('GetInfoRequest', array(), $options);
		return $result->GetInfoResponse;
	}

	/**
	 * Get preferences for the authenticated account 
	 *
	 * @return mixed
	 */
	public function getPrefs(array $prefs = array())
	{
		$params = array();
		$arrPrefs = $this->_attributes($prefs, 'name');
		if(count($arrPrefs))
		{
			$params['pref'] = $arrPrefs;
		}
		$result = $this->_client->soapRequest('GetPrefsRequest', $params);
		return $result->GetPrefsResponse;
	}

	/**
	 * Get account level rights. 
	 *
	 * @param  array $ace Specify Access Control Entries. 
	 * @return mixed
	 */
	public function getRights(array $ace = array())
	{
		$params = array();
		if(count($ace))
		{
			$params['ace'] = array();
			foreach ($ace as $right)
			{
				$params['ace'][] = array('right' => $right);
			}
		}
		$result = $this->_client->soapRequest('GetRightsRequest', $params);
		return $result->GetRightsResponse;
	}

	/**
	 * Get SMIME Public Certificates Stores specified in <store> will be attempted in the order they appear in the comma separated list.
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
	public function getSMIMEPublicCerts(array $stores, array $emails = array())
	{
		$lookupOpts = array('ANY', 'ALL');

		$storeLO = (isset($stores['storeLookupOpt']) AND in_array($stores['storeLookupOpt'], $lookupOpts)) ? $stores['storeLookupOpt'] : 'ANY';
		$sourceLO = (isset($stores['sourceLookupOpt']) AND in_array($stores['sourceLookupOpt'], $lookupOpts)) ? $stores['sourceLookupOpt'] : 'ALL';
		$value = isset($stores['_']) ? $stores['_'] : 'GAL';
		$params = array(
			'store' => array(
				'storeLookupOpt' => $storeLO,
				'sourceLookupOpt' => $sourceLO,
				'_' => $value,
			),
		);
		if(count($emails))
		{
			$params['email'] = array();
			foreach ($emails as $email)
			{
				$params['email'][] = $email;
			}
		}
		$result = $this->_client->soapRequest('GetSMIMEPublicCertsRequest', $params);
		return $result->GetSMIMEPublicCertsResponse;
	}

	/**
	 * Get information about published shares
	 *
	 * @param  string $owner       Specifies the owner of the share
	 * @param  string $grantee     Filter by the specified grantee type
	 * @param  bool   $internal    Flags that have been proxied to this server because the specified "owner account" is homed here. Do not proxy in this case. (Used internally by ZCS)
	 * @param  bool   $includeSelf Flag whether own shares should be included. 0 if shares owned by the requested account should not be included in the response. 1 (default) include shares owned by the requested account
	 * @return mixed
	 */
	public function getShareInfo($owner = '', array $grantee = array(), $internal = TRUE, $includeSelf = TRUE)
	{
		$options = array(
			'internal' => (bool) $internal ? 1 : 0,
			'includeSelf' => (bool) $includeSelf ? 1 : 0,
		);
		$params = array();
		if(!empty($owner))
		{
			$params['owner'] = array(
				'by' => 'name',
				'_' => $owner,
			);			
		}
		if(count($grantee))
		{
			$params['grantee'] = $grantee;
		}
		$result = $this->_client->soapRequest('GetShareInfoRequest', $params, $options);
		return $result->GetShareInfoResponse;
	}

	/**
	 * Get Signatures associated with an account
	 *
	 * @return mixed
	 */
	public function getSignatures()
	{
		$result = $this->_client->soapRequest('GetSignaturesRequest');
		return $result->GetSignaturesResponse;
	}

	/**
	 * Get Signatures associated with an account
	 * Note: This request will return a SOAP fault if the zimbraSoapExposeVersion server/globalconfig attribute is set to FALSE.
	 *
	 * @return mixed
	 */
	public function getVersionInfo()
	{
		$result = $this->_client->soapRequest('GetVersionInfoRequest');
		return $result->GetVersionInfoResponse;
	}

	/**
	 * Get the anti-spam WhiteList and BlackList addresses
	 *
	 * @return mixed
	 */
	public function getWhiteBlackList()
	{
		$result = $this->_client->soapRequest('GetWhiteBlackListRequest');
		return $result->GetWhiteBlackListResponse;
	}

	/**
	 * Grant account level rights
	 *
	 * @param  array $ace Specify Access Control Entries
	 * @return mixed
	 */
	public function grantRights(array $ace)
	{
		$aceKeys = array('zid', 'gt', 'right', 'd', 'key', 'pw', 'deny', 'chkgt');
		$params['ace'] = array();
		foreach ($ace as $key => $value)
		{
			if(in_array($key, $aceKeys))
			{
				$params['ace'][$key] = $value;
			}
		}

		$result = $this->_client->soapRequest('GrantRightsRequest', $params);
		return $result->GrantRightsResponse;
	}

	/**
	 * Modify an Identity
	 *
	 * @param  string $name  Identity name
	 * @param  array  $attrs Attributes
	 * @return mixed
	 */
	public function modifyIdentity($name, array $attrs = array())
	{
		$params = array(
			'identity' => array(
				'name' => $name,
			),
		);
		$attributes = $this->_attributes($attrs);
		if(count($attributes))
		{
			$params['identity']['a'] = $attributes;
		}
		$result = $this->_client->soapRequest('ModifyIdentityRequest', $params);
		return $result->ModifyIdentityResponse;
	}

	/**
	 * Modify preferences
	 *
	 * @param  array $prefs Specify the preferences to be modified
	 * @return mixed
	 */
	public function modifyPrefs(array $prefs = array())
	{
		$params = array();
		if(count($prefs))
		{
			$params['pref'] = array();
			foreach ($prefs as $name => $value)
			{
				$params['pref'][] = array(
					'name' => $name,
					'_' => $value,
				);
			}
		}
		$result = $this->_client->soapRequest('ModifyPrefsRequest', $params);
		return $result->ModifyPrefsResponse;
	}

	/**
	 * Modify properties related to zimlets
	 *
	 * @param  string $name      Zimlet name
	 * @param  array  $prop_name Property name
	 * @param  array  $value     Property value
	 * @return mixed
	 */
	public function modifyProperties($name, $prop_name, $value)
	{
		$params = array(
			'prop' => array(
				'zimlet' => $name,
				'name' => $prop_name,
				'_' => $value,
			),
		);
		$result = $this->_client->soapRequest('ModifyPropertiesRequest', $params);
		return $result->ModifyPropertiesResponse;
	}

	/**
	 * Change attributes of the given signature
	 * Only the attributes specified in the request are modified.
	 * Note: The Server identifies the signature by id, if the name attribute is present and is different from the current name of the signature, the signature will be renamed.
	 *
	 * @param  string $name    Identity name
	 * @param  string $content Content of the signature
	 * @param  string $type    Content type of the signature
	 * @return mixed
	 */
	public function modifySignature($name, $content = '', $type = 'text/plain')
	{
		$params = array(
			'signature' => array(
				'name' => $name,
				'content' => array(
					'type' => $type,
					'_' => $content,
				),
			),
		);
		$result = $this->_client->soapRequest('ModifySignatureRequest', $params);
		return $result->ModifySignatureResponse;
	}

	/**
	 * Modify the anti-spam WhiteList and BlackList addresses
	 * Note: If no <addr> is present in a list, it means to remove all addresses in the list. 
	 *
	 * @param  array $whiteList Identity name
	 * @param  array $blackList Content of the signature
	 * @return mixed
	 */
	public function modifyWhiteBlackList(array $whiteList, array $blackList = array())
	{
		$params['whiteList'] = array();
		foreach ($whiteList as $op => $value)
		{
			$wl['whiteList'][] = array(
				'addr' => array(
					'op' => $op,
					'_' => $value,
				),
			);
		}
		$params['blackList'] = array();
		foreach ($blackList as $op => $value)
		{
			$wl['whiteList'][] = array(
				'addr' => array(
					'op' => $op,
					'_' => $value,
				),
			);
		}
		$result = $this->_client->soapRequest('ModifyWhiteBlackListRequest', $params);
		return $result->ModifyWhiteBlackListResponse;				
	}

	/**
	 * Modify Zimlet Preferences
	 *
	 * @param  string $name     Zimlet name
	 * @param  bool   $presence Zimlet presence setting
	 * @return mixed
	 */
	public function modifyZimletPrefs($name, $presence = TRUE)
	{
		$params = array(
			'zimlet' => array(
				'name' => $name,
				'presence' => ($presence) ? 'enabled' : 'disabled',
			),
		);
		$result = $this->_client->soapRequest('ModifyZimletPrefsRequest', $params);
		return $result->ModifyZimletPrefsResponse;		
	}

	/**
	 * Revoke account level rights
	 *
	 * @param  array $ace Specify Access Control Entries
	 * @return mixed
	 */
	public function revokeRights(array $ace = array())
	{
		$aceKeys = array('zid', 'gt', 'right', 'd', 'key', 'pw', 'deny', 'chkgt');
		$params['ace'] = array();
		foreach ($ace as $key => $value)
		{
			if(in_array($key, $aceKeys))
			{
				$params['ace'][$key] = $value;
			}
		}
		$result = $this->_client->soapRequest('RevokeRightsRequest', $params);
		return $result->RevokeRightsResponse;
	}

	/**
	 * Search Global Address List (GAL) for calendar resources
	 * "attrs" attribute - comma-separated list of attrs to return ("displayName", "zimbraId", "zimbraCalResType")
	 *
	 * @param  array  $conds   Search filter conditions
	 * @param  array  $cursor  Cursor specification
	 * @param  array  $options Search options
	 * @param  string $name    If specified, passed through to the GAL search as the search key
	 * @param  string $locale  Client locale identification.
	 * @return mixed
	 */
	public function searchCalendarResources(array $conds, array $cursor = array(), array $options = array(), $name = '', $locale = '')
	{
		$optionKeys = array('quick', 'sortBy', 'limit', 'offset', 'galAcctId', 'attrs');
		$attrs = array();
		foreach ($options as $key => $value)
		{
			if(in_array($key, $optionKeys))
			{
				$attrs[$key] = $value;
			}
		}

		$params['searchFilter'] = array();
		if(isset($conds['cond']) AND is_array($conds['cond']))
		{
			$params['searchFilter']['cond'] = $this->_processCondFilter($conds['cond']);
		}
		elseif(isset($conds['conds']) AND is_array($conds['conds']))
		{
			$params['searchFilter']['conds'] = $this->_processCondsFilter($conds['conds']);
		}

		$cursorKeys = array('id', 'sortVal', 'endSortVal', 'includeOffset');
		$params['cursor'] = array();
		foreach ($cursor as $key => $value)
		{
			if(in_array($key, $cursorKeys))
			{
				$params['cursor'][$key] = $value;
			}
		}

		if(!empty($locale)) $params['locale'] = $locale;
		if(!empty($name)) $params['name'] = $name;
		$result = $this->_client->soapRequest('SearchCalendarResourcesRequest', $params, $attrs);
		return $result->SearchCalendarResourcesResponse;
	}


	/**
	 * Search Global Address List (GAL)
	 *
	 * @param  array  $conds   Search filter conditions.
	 * @param  array  $cursor  Cursor specification.
	 * @param  array  $options Search options.
	 * @param  string $locale  Client locale identification.
	 * @return mixed
	 */
	public function searchGal(array $conds = array(), array $cursor = array(), array $options = array(), $locale = '')
	{
		$optionKeys = array('ref', 'name', 'type', 'needExp', 'needIsOwner', 'needIsMember', 'needSMIMECerts', 'galAcctId', 'quick', 'sortBy', 'limit', 'offset');
		$attrs = array();
		foreach ($options as $key => $value)
		{
			if(in_array($key, $optionKeys))
			{
				$attrs[$key] = $value;
			}
		}

		$params['searchFilter'] = array();
		if(isset($conds['cond']) AND is_array($conds['cond']))
		{
			$params['searchFilter']['cond'] = $this->_processCondFilter($conds['cond']);
		}
		elseif(isset($conds['conds']) AND is_array($conds['conds']))
		{
			$params['searchFilter']['conds'] = $this->_processCondsFilter($conds['conds']);
		}

		$cursorKeys = array('id', 'sortVal', 'endSortVal', 'includeOffset');
		$params['cursor'] = array();
		foreach ($cursor as $key => $value)
		{
			if(in_array($key, $cursorKeys))
			{
				$params['cursor'][$key] = $value;
			}
		}

		if(!empty($locale)) $params['locale'] = $locale;
		$result = $this->_client->soapRequest('SearchGalRequest', $params, $attrs);
		return $result->SearchGalResponse;
	}

	/**
	 * Subscribe to a distribution list
	 *
	 * @param  string $name      Distribution list name
	 * @param  bool   $subscribe Subscribe
	 * @return mixed
	 */
	public function subscribeDistributionList($name, $subscribe = TRUE)
	{
		$params = array(
			'dl' => array(
				'by' => 'name',
				'_' => $name,
			),
		);
		$result = $this->_client->soapRequest('SubscribeDistributionListRequest', $params, array('op' => ($$subscribe) ? 'subscribe' : 'unsubscribe'));
		return $result->SubscribeDistributionListResponse;
	}

	/**
	 * Synchronize with the Global Address List
	 *
	 * @param  string $token     The previous synchronization token if applicable
	 * @param  string $galAcctId GAL sync account ID
	 * @return mixed
	 */
	public function syncGal($token, $galAcctId = '')
	{
		$params = array(
			'token' => 'token',
			'galAcctId' => $galAcctId,
		);
		$result = $this->_client->soapRequest('SyncGalRequest', array(), $params);
		return $result->SyncGalResponse;
	}

	private function _setDlOwnerGrantee(array $params = array())
	{
		$arr = array();
		if(count($params))
		{
			$validTypes = array('usr', 'grp', 'egp', 'all', 'dom', 'gst', 'key', 'pub', 'email');
			$type = (isset($params['type']) AND in_array($params['type'], $validTypes)) ? $params['type'] : 'all';
			$arr = array(
				'type' => $type,
				'by' => 'name',
				'_' => isset($params['_']) ? $params['_'] : '',
			);
		}
		return $arr;
	}
}
<?php
class ZAP_Tests_API_AccountTest extends PHPUnit_Framework_TestCase
{
	private $_api;
	private $_authToken;

	public function setUp()
	{
		$driver = ZAP::setting('driver');
		$location = ZAP::setting('location');
		$this->_api = ZAP_API_Account::factory($driver, $location);
	}

	public function testAuth()
	{
		$account = ZAP::setting('account');
		$password = ZAP::setting('password');
		$result = $this->_api->auth($account, $password);
		$this->assertObjectHasAttribute('authToken', $result);
		return $result->authToken;
	}

    /**
     * @depends testAuth
     */
	public function testAuthByToken($authToken)
	{
		$account = ZAP::setting('account');
		$result = $this->_api->authByToken($account, $authToken);
		$this->assertObjectHasAttribute('authToken', $result);
	}

	public function testPreAuth()
	{
		try
		{
			$account = ZAP::setting('account');
			$preAuthKey = ZAP::setting('preAuthKey');
			$result = $this->_api->preAuth($account, $preAuthKey);
			$this->assertObjectHasAttribute('authToken', $result);			
		}
		catch(Exception $ex)
		{
            $this->markTestSkipped((string)$ex);
		}
	}

	public function testAutoCompleteGal()
	{
		$account = ZAP::setting('account');
		$result = $this->_api->autoCompleteGal($account);
		$this->assertObjectHasAttribute('cn', $result);
		$this->assertObjectHasAttribute('id', $result->cn);
	}

	public function testCheckLicense()
	{
		try
		{
			$account = ZAP::setting('account');
			$result = $this->_api->checkLicense('account');
			$this->assertObjectHasAttribute('status', $result);
		}
		catch(Exception $ex)
		{
            $this->markTestSkipped((string)$ex);
		}
	}

	public function testCheckRights()
	{
		$account = ZAP::setting('account');
		$result = $this->_api->checkRights('account', $account, array('sendAs'));
		$this->assertObjectHasAttribute('target', $result);
		$this->assertObjectHasAttribute('right', $result->target);
	}

	public function testCreateDistributionList()
	{
		try
		{
			$domain = ZAP::setting('domain');
			$result = $this->_api->createDistributionList('test@'.$domain);
			$this->assertObjectHasAttribute('dl', $result);
		}
		catch(Exception $ex)
		{
            $this->markTestSkipped((string)$ex);
		}
	}

	public function testCreateIdentity()
	{
		$result = $this->_api->createIdentity('Test');
		$this->assertObjectHasAttribute('identity', $result);
	}

	public function testCreateSignature()
	{
		$result = $this->_api->createIdentity('Test', 'Test Content');
		$this->assertObjectHasAttribute('signature', $result);
	}

	public function testDeleteIdentity()
	{
		$result = $this->_api->deleteIdentity('Test');
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testDeleteSignature()
	{
		$result = $this->_api->deleteSignature('Test');
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testDiscoverRights()
	{
		$result = $this->_api->discoverRights(array('sendAs'));
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testDistributionListAction()
	{
		try
		{
			$dl = ZAP::setting('dl');
			$result = $this->_api->distributionListAction($dl, array());
			$this->assertInstanceOf('stdClass', $result);
		}
		catch(Exception $ex)
		{
            $this->markTestSkipped((string)$ex);
		}
	}

	public function testGetAccountDistributionLists()
	{
		$result = $this->_api->getAccountDistributionLists();
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testGetAccountInfo()
	{
		$account = ZAP::setting('account');
		$result = $this->_api->getAccountInfo($account);
		$this->assertObjectHasAttribute('name', $result);
	}

	public function testGetAllLocales()
	{
		$result = $this->_api->getAllLocales();
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testGetAvailableCsvFormats()
	{
		$result = $this->_api->getAvailableCsvFormats();
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testGetAvailableLocales()
	{
		$result = $this->_api->getAvailableLocales();
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testGetAvailableSkins()
	{
		$result = $this->_api->getAvailableSkins();
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testGetDistributionList()
	{
		try
		{
			$dl = ZAP::setting('dl');
			$result = $this->_api->getDistributionList($dl);
			$this->assertInstanceOf('stdClass', $result);
		}
		catch(Exception $ex)
		{
            $this->markTestSkipped((string)$ex);
		}
	}

	public function testGetDistributionListMembers()
	{
		try
		{
			$dl = ZAP::setting('dl');
			$result = $this->_api->getDistributionListMembers($dl);
			$this->assertInstanceOf('stdClass', $result);
		}
		catch(Exception $ex)
		{
            $this->markTestSkipped((string)$ex);
		}
	}

	public function testGetIdentities()
	{
		$result = $this->_api->getIdentities();
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testGetInfo()
	{
		$result = $this->_api->getInfo();
		$this->assertObjectHasAttribute('id', $result);
		$this->assertObjectHasAttribute('name', $result);
	}

	public function testGetPrefs()
	{
		$result = $this->_api->getPrefs();
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testGetRights()
	{
		$result = $this->_api->getRights(array('sendAs'));
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testGetSMIMEPublicCerts()
	{
		try
		{
			$result = $this->_api->getSMIMEPublicCerts(array('CONTACT'));
			$this->assertInstanceOf('stdClass', $result);
			$this->assertObjectHasAttribute('certs', $result);
		}
		catch(Exception $ex)
		{
            $this->markTestSkipped((string)$ex);
		}
	}

	public function testGetShareInfo()
	{
		$result = $this->_api->getShareInfo();
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testGetSignatures()
	{
		$result = $this->_api->getSignatures();
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testGetVersionInfo()
	{
		try
		{
			$result = $this->_api->getVersionInfo();
			$this->assertInstanceOf('stdClass', $result);
		}
		catch(Exception $ex)
		{
            $this->markTestSkipped((string)$ex);
		}
	}

	public function testGetWhiteBlackList()
	{
		$result = $this->_api->getWhiteBlackList();
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testGrantRights()
	{
		$result = $this->_api->grantRights();
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testModifyIdentity()
	{
		$result = $this->_api->modifyIdentity('Test');
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testModifyPrefs()
	{
		$result = $this->_api->modifyPrefs();
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testModifyProperties()
	{
		$result = $this->_api->modifyProperties('test', 'test', 'test');
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testModifySignature()
	{
		$result = $this->_api->modifySignature('Test', 'Test Content');
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testModifyWhiteBlackList()
	{
		$result = $this->_api->modifyWhiteBlackList(array('+' => 'foo', '-' => 'bar'));
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testModifyZimletPrefs()
	{
		$result = $this->_api->modifyZimletPrefs();
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testRevokeRights()
	{
		$entry = array('right' => 'viewFreeBusy', 'gt' => 'all');
		$result = $this->_api->revokeRights(array($entry));
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testSubscribeDistributionList()
	{
		$dl = ZAP::setting('dl');
		$result = $this->_api->subscribeDistributionList($dl);
		$this->assertInstanceOf('stdClass', $result);
		$this->assertObjectHasAttribute('status', $result);
	}

	public function testLogout()
	{
		$result = $this->_api->logout();
		$this->assertInstanceOf('stdClass', $result);
	}

	public function testChangePassword()
	{
		$password = ZAP::setting('password');
		$result = $this->_api->changePassword($password, $password);
		$this->assertObjectHasAttribute('authToken', $result);
	}
}
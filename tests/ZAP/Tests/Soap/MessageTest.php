<?php
class ZAP_Tests_Soap_MessageTest extends PHPUnit_Framework_TestCase
{
	public function testSoapMessage()
	{
		$authToken = md5('authToken');
        $request = '<?xml version="1.0"?>'."\n"
			.'<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" '
						 .'xmlns:urn="urn:zimbra">'
				.'<env:Header>'
					.'<urn:context>'
						.'<urn:authToken>'.$authToken.'</urn:authToken>'
					.'</urn:context>'
				.'</env:Header>'
				.'<env:Body>'
					.'<urn:testFunc echo="1">'
						.'<urn:param0>Hello</urn:param0>'
						.'<urn:param1 title="Mr">Test</urn:param1>'
					.'</urn:testFunc>'
				.'</env:Body>'
			.'</env:Envelope>';
		$message = new ZAP_Soap_Message;
		$params = array(
			'param0' => 'Hello',
			'param1' => array(
				'title' => 'Mr',
				'_' => 'Test',
			)
		);
		$message->addHeader('authToken', $authToken);
		$message->setBody('testFunc', array('echo' => 1), $params);
		$this->assertEquals($request, (string) $message);
	}
}
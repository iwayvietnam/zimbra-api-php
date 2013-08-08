<?php
require '../src/ZAP.php';

function autoloadTest($className)
{
	if (0 !== strpos($class, 'ZAP'))
	{
		return false;
	}
	$path = dirname(__FILE__).DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $class).'.php';
	if (!file_exists($path))
	{
		return false;
	}
	require_once $path;
}
spl_autoload_register('autoloadTest');

ZAP::setting(array(
	'driver' => 'soap',
	'location' => 'https://localhost/service/soap',
	'account' => 'user@localhost.localdomain',
	'password' => 'secret@123',
	'preAuthKey' => 'preAuthKey',
	'dl' => 'dl@localhost.localdomain',
	'domain' => 'localhost.localdomain',
));

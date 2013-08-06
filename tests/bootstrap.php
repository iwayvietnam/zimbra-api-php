<?php
require '../src/ZAP.php';
ZAP::registerAutoload();

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
	'driver' => 'wsdl',
	'server' => 'localhost',
	'port' => 443,
	'account' => 'user@localhost.localdomain',
	'password' => 'secret',
	'preAuthKey' => 'secret',
	'adminServer' => 'localhost',
	'adminPort' => 7071,
	'dl' => 'dl@localhost.localdomain',
	'domain' => 'localhost.localdomain',
));

<?php
// This switches working directory to the root directory of MediaWiki.
// This is essential for the page to work
chdir('../..');

// Start up MediaWiki
require_once dirname(__FILE__) . '/../../includes/PHPVersionCheck.php';
wfEntryPointCheck('avatar.php');

require __DIR__ . '/../../includes/WebStart.php';

// URL safety checks
if (!$wgRequest->checkUrlExtension()) {
	return;
}

// Parse request
$par = urldecode($wgRequest->getRawQueryString());

global $wgDefaultAvatar, $wgDefaultAvatarRes;
$path = $wgDefaultAvatar;

if ($par) {
	// Parse parts
	$parts = explode('/', $par, 2);
	$username = $parts[0];
	$res = count($parts) === 2 ? \Avatar\Avatars::normalizeResolution($parts[1]) : $wgDefaultAvatarRes;
	$user = User::newFromName($username);
	$path = \Avatar\Avatars::getAvatar($user, $res);
}

$response = $wgRequest->response();

// We use send custom header, in order to control cache
$response->statusHeader('302');

// Cache longer time if it is not the default avatar
// As it is unlikely to be deleted
if ($path === $wgDefaultAvatar) {
	$response->header('Cache-Control: public, max-age=3600');
} else {
	$response->header('Cache-Control: public, max-age=86400');
}

$response->header('Location: ' . $path);

echo $par;

$mediawiki = new MediaWiki();
$mediawiki->doPostOutputShutdown('fast');

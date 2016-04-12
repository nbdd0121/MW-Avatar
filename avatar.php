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

$query = $wgRequest->getQueryValues();

global $wgDefaultAvatar;
$path = $wgDefaultAvatar;

if (isset($query['user'])) {
	$username = $query['user'];

	if (isset($query['res'])) {
		$res = \Avatar\Avatars::normalizeResolution($query['res']);
	} else {
		global $wgDefaultAvatarRes;
		$res = $wgDefaultAvatarRes;
	}

	$user = User::newFromName($username);
	if ($user) {
		$path = \Avatar\Avatars::getAvatar($user, $res);
	}
}

// ver will be propagated to the relocated image
if (isset($query['ver'])) {
	if (strpos($path, '?') !== false) {
		$path .= '&ver=' . $query['ver'];
	} else {
		$path .= '?ver=' . $query['ver'];
	}
}

$response = $wgRequest->response();

// We use send custom header, in order to control cache
$response->statusHeader('302');

if (!isset($query['nocache'])) {
	// Cache longer time if it is not the default avatar
	// As it is unlikely to be deleted
	if ($path === $wgDefaultAvatar) {
		$response->header('Cache-Control: public, max-age=3600');
	} else {
		$response->header('Cache-Control: public, max-age=86400');
	}
}

$response->header('Location: ' . $path);

$mediawiki = new MediaWiki();
$mediawiki->doPostOutputShutdown('fast');

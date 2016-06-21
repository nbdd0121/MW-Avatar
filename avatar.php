<?php

// For some configurations, extensions are symbolic linked
// This is the workaround for ../..
$dir = dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])));

// This switches working directory to the root directory of MediaWiki.
// This is essential for the page to work
chdir($dir);

// Start up MediaWiki
require_once 'includes/PHPVersionCheck.php';
wfEntryPointCheck('avatar.php');

require 'includes/WebStart.php';

// URL safety checks
if (!$wgRequest->checkUrlExtension()) {
	return;
}

$query = $wgRequest->getQueryValues();

$path = null;

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

$response = $wgRequest->response();

// In order to maximize cache hit and due to 
// fact that default avatar might be external,
// always redirect
if ($path === null) {
	// We use send custom header, in order to control cache
	$response->statusHeader('302');

	if (!isset($query['nocache'])) {
		// Cache longer time if it is not the default avatar
		// As it is unlikely to be deleted
		$response->header('Cache-Control: public, max-age=3600');
	}

	global $wgDefaultAvatar;
	$response->header('Location: ' . $wgDefaultAvatar);

	$mediawiki = new MediaWiki();
	$mediawiki->doPostOutputShutdown('fast');
	exit;
}

switch($wgAvatarServingMethod) {
case 'readfile':
	global $wgAvatarUploadDirectory;
	$response->header('Cache-Control: public, max-age=86400');
	$response->header('Content-Type: image/png');
	readfile($wgAvatarUploadDirectory . $path);
	break;
case 'accel':
	global $wgAvatarUploadPath;
	$response->header('Cache-Control: public, max-age=86400');
	$response->header('Content-Type: image/png');
	$response->header('X-Accel-Redirect: ' . $wgAvatarUploadPath . $path);
	break;
case 'sendfile':
	global $wgAvatarUploadDirectory;
	$response->header('Cache-Control: public, max-age=86400');
	$response->header('Content-Type: image/png');
	$response->header('X-SendFile: ' . $wgAvatarUploadDirectory . $path);
	break;
case 'redirection':
default:
	$ver = '';

	// ver will be propagated to the relocated image
	if (isset($query['ver'])) {
		$ver = $query['ver'];
	} else {
		global $wgVersionAvatar;
		if ($wgVersionAvatar) {
			global $wgAvatarUploadDirectory;
			$ver = filemtime($wgAvatarUploadDirectory . $path);
		}
	}

	if ($ver) {
		if (strpos($path, '?') !== false) {
			$path .= '&ver=' . $ver;
		} else {
			$path .= '?ver=' . $ver;
		}
	}

	// We use send custom header, in order to control cache
	$response->statusHeader('302');

	if (!isset($query['nocache'])) {
		// Cache longer time if it is not the default avatar
		// As it is unlikely to be deleted
		$response->header('Cache-Control: public, max-age=86400');
	}

	global $wgAvatarUploadPath;
	$response->header('Location: ' . $wgAvatarUploadPath . $path);
	break;
}

$mediawiki = new MediaWiki();
$mediawiki->doPostOutputShutdown('fast');

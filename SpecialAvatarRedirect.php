<?php
namespace Avatar;

use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPage;

class SpecialAvatarRedirect extends SpecialPage {

	public function __construct() {
		parent::__construct('AvatarRedirect');
	}

	public function isListed() {
		return false;
	}

	public function execute($subPage) {
		$request = $this->getRequest();
		$response = $request->response();
		$this->getOutput()->disable();

		$username = $request->getVal('user', '');
		$user = $username === '' ? null : MediaWikiServices::getInstance()
			->getUserFactory()->newFromName($username);
		$config = $this->getConfig();
		$requestedRes = $request->getVal('res');
		$res = $requestedRes === null
			? $config->get('DefaultAvatarRes')
			: Avatars::normalizeResolution($requestedRes);
		$path = $user ? Avatars::getAvatar($user, $res) : null;

		if ($path === null) {
			$response->statusHeader(302);
			if ($request->getVal('nocache') === null) {
				$response->header('Cache-Control: public, max-age=3600');
			}
			$response->header('Location: ' . $config->get('DefaultAvatar'));
			return;
		}

		$publicPath = $config->get('AvatarUploadPath') . $path;
		$file = $config->get('AvatarUploadDirectory') . $path;
		$mode = $config->get('AvatarServingMethod');
		if (in_array($mode, ['readfile', 'accel', 'sendfile'], true)) {
			$response->header('Cache-Control: public, max-age=86400');
			$response->header('Content-Type: image/png');
			switch ($mode) {
			case 'readfile':
				readfile($file);
				break;
			case 'accel':
				$response->header('X-Accel-Redirect: ' . $publicPath);
				break;
			case 'sendfile':
				$response->header('X-SendFile: ' . $file);
				break;
			}
			return;
		}

		$version = $request->getVal('ver');
		if ($version === null && $config->get('VersionAvatar')) {
			$version = filemtime($file);
		}
		if ($version) {
			$publicPath .= (str_contains($publicPath, '?') ? '&' : '?') . 'ver=' . rawurlencode($version);
		}
		$response->statusHeader(302);
		if ($request->getVal('nocache') === null) {
			$response->header('Cache-Control: public, max-age=86400');
		}
		$response->header('Location: ' . $publicPath);
	}
}

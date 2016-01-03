<?php
namespace Avatar;

class SpecialAvatar extends \SpecialPage {

	public function __construct() {
		parent::__construct('Avatar');
	}

	public function execute($par) {
		global $wgDefaultAvatar, $wgDefaultAvatarRes;
		$path = $wgDefaultAvatar;

		if ($par) {
			// Parse parts
			$parts = explode('/', $par, 2);
			$username = $parts[0];
			$res = $parts[1] ?: $wgDefaultAvatarRes;

			$user = \User::newFromName($username);
			// If user exists
			if ($user && $user->getId()) {
				global $wgUploadDirectory, $wgUploadPath;
				$avatarPath = "/avatars/{$user->getId()}/$res.png";

				if (file_exists($wgUploadDirectory . $avatarPath)) {
					$path = $wgUploadPath . $avatarPath;
				} else if ($res != $wgDefaultAvatarRes) {
					// Use != here, instead of !==, is intended
					$avatarPath = "/avatars/{$user->getId()}/$wgDefaultAvatarRes.png";
					if (file_exists($wgUploadDirectory . $avatarPath)) {
						$path = $wgUploadPath . $avatarPath;
					}
				}
			}
		}

		$this->getOutput()->disable();
		$response = $this->getRequest()->response();

		// We use send custom header, in order to control cache
		$response->statusHeader('302');
		$response->header('Content-Type: text/html; charset=utf-8');
		$response->header('Cache-Control: public, max-age=86400');
		// $response->header('Cache-Control: no-cache');
		$response->header('Location: ' . $path);
	}

	public function isListed() {
		return false;
	}
}
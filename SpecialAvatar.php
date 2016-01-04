<?php
namespace Avatar;

class SpecialAvatar extends \SpecialPage {

	public function __construct() {
		parent::__construct('Avatar');
	}

	private static function normalizeResolution($res) {
		if ($res === 'original') {
			return 'original';
		}
		$res = intval($res);

		global $wgAllowedAvatarRes;
		foreach ($wgAllowedAvatarRes as $r) {
			if ($res <= $r) {
				return $r;
			}
		}

		return 'original';
	}

	public function execute($par) {
		global $wgDefaultAvatar, $wgDefaultAvatarRes;
		$path = $wgDefaultAvatar;

		if ($par) {
			// Parse parts
			$parts = explode('/', $par, 2);
			$username = $parts[0];
			$res = count($parts) === 2 ? self::normalizeResolution($parts[1]) : $wgDefaultAvatarRes;

			$user = \User::newFromName($username);

			// If user exists
			if ($user && $user->getId()) {
				global $wgUploadDirectory, $wgUploadPath;
				$avatarPath = "/avatars/{$user->getId()}/$res.png";

				// Check if requested avatar thumbnail exists
				if (file_exists($wgUploadDirectory . $avatarPath)) {
					$path = $wgUploadPath . $avatarPath;
				} else if ($res !== 'original') {
					// Dynamically generate upon request
					$originalAvatarPath = "/avatars/{$user->getId()}/original.png";
					if (file_exists($wgUploadDirectory . $originalAvatarPath)) {
						$image = Thumbnail::open($wgUploadDirectory . $originalAvatarPath);
						$image->createThumbnail($res, $wgUploadDirectory . $avatarPath);
						$image->cleanup();
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
		// $response->header('Cache-Control: public, max-age=86400');
		$response->header('Cache-Control: no-cache');
		$response->header('Location: ' . $path);
	}

	public function isListed() {
		return false;
	}
}
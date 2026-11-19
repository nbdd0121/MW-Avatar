<?php
namespace Avatar;

use MediaWiki\User\UserIdentity;

class Avatars {

	public static function getLinkFor($username, $res = false) {
		global $wgScriptPath;
		$query = ['user' => $username];
		if ($res !== false) {
			$query['res'] = $res;
		}
		return "$wgScriptPath/extensions/Avatar/avatar.php?" . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
	}

	public static function normalizeResolution($res) {
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

	public static function getAvatar(UserIdentity $user, $res) {
		$path = null;

		// If user exists
		if ($user->getId()) {
			global $wgAvatarUploadDirectory;
			$avatarPath = "/{$user->getId()}/$res.png";

			// Check if requested avatar thumbnail exists
			if (file_exists($wgAvatarUploadDirectory . $avatarPath)) {
				$path = $avatarPath;
			} else if ($res !== 'original') {
				// Dynamically generate upon request
				$originalAvatarPath = "/{$user->getId()}/original.png";
				if (file_exists($wgAvatarUploadDirectory . $originalAvatarPath)) {
					$image = Thumbnail::open($wgAvatarUploadDirectory . $originalAvatarPath);
					if ($image !== null) {
						$image->createThumbnail($res, $wgAvatarUploadDirectory . $avatarPath);
						$path = $avatarPath;
					}
				}
			}
		}

		return $path;
	}

	public static function hasAvatar(UserIdentity $user) {
		return self::getAvatar($user, 'original') !== null;
	}

	public static function deleteAvatar(UserIdentity $user) {
		global $wgAvatarUploadDirectory;
		$dirPath = $wgAvatarUploadDirectory . "/{$user->getId()}/";
		if (!is_dir($dirPath)) {
			return false;
		}
		$files = glob($dirPath . '*', GLOB_MARK);
		foreach ($files as $file) {
			unlink($file);
		}
		rmdir($dirPath);
		return true;
	}

}

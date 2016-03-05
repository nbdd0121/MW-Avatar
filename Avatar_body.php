<?php
namespace Avatar;

class Avatars {

	public static function getLinkFor($username, $res = false) {
		global $wgScriptPath;
		$path = "$wgScriptPath/extensions/Avatar/avatar.php?user=$username";
		if ($res !== false) {
			return $path . '&res=' . $res;
		} else {
			return $path;
		}
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

	public static function getAvatar(\User $user, $res) {
		global $wgDefaultAvatar, $wgDefaultAvatarRes;
		$path = $wgDefaultAvatar;

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

		return $path;
	}

	public static function hasAvatar(\User $user) {
		global $wgDefaultAvatar;
		return self::getAvatar($user, 'original') !== $wgDefaultAvatar;
	}

	public static function deleteAvatar(\User $user) {
		global $wgUploadDirectory;
		$dirPath = $wgUploadDirectory . "/avatars/{$user->getId()}/";
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
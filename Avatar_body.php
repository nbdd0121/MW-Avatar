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
		$userId = $user->getId();
		if (!$userId) {
			return null;
		}

		global $wgAvatarUploadDirectory;
		$directory = "$wgAvatarUploadDirectory/$userId/";
		$original = $directory . 'original.png';
		if (!is_file($original)) {
			return null;
		}

		$avatarPath = "/$userId/$res.png";
		$avatarFile = $wgAvatarUploadDirectory . $avatarPath;
		if (is_file($avatarFile)) {
			return $avatarPath;
		}
		if ($res === 'original') {
			return null;
		}

		return self::withAvatarLock($directory, static function () use ($avatarFile, $avatarPath, $original, $res) {
			clearstatcache();
			if (!is_file($avatarFile)) {
				$image = is_file($original) ? Thumbnail::open($original) : null;
				if ($image === null) {
					return null;
				}
				$image->createThumbnail($res, $avatarFile);
			}
			return $avatarPath;
		});
	}

	public static function hasAvatar(UserIdentity $user) {
		return self::getAvatar($user, 'original') !== null;
	}

	public static function storeAvatar(UserIdentity $user, Thumbnail $image, int $maxResolution): void {
		global $wgAvatarUploadDirectory;
		$directory = $wgAvatarUploadDirectory . "/{$user->getId()}/";
		if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
			throw new \RuntimeException('Could not create the avatar upload directory');
		}
		self::withAvatarLock($directory, static function () use ($directory, $image, $maxResolution) {
			self::clearThumbnails($directory);
			$image->createThumbnail($maxResolution, $directory . 'original.png');
		});
	}

	public static function deleteAvatar(UserIdentity $user): bool {
		global $wgAvatarUploadDirectory;
		$directory = $wgAvatarUploadDirectory . "/{$user->getId()}/";
		$original = $directory . 'original.png';
		if (!is_file($original)) {
			return false;
		}

		return self::withAvatarLock($directory, static function () use ($directory, $original) {
			clearstatcache();
			if (!is_file($original)) {
				return false;
			}
			self::clearThumbnails($directory);
			if (!unlink($original)) {
				throw new \RuntimeException('Could not delete the avatar');
			}
			return true;
		});
	}

	private static function clearThumbnails(string $directory): void {
		$files = glob($directory . '[0-9]*.png');
		if ($files === false) {
			throw new \RuntimeException('Could not list avatar thumbnails');
		}
		foreach ($files as $file) {
			if (!@unlink($file) && is_file($file)) {
				throw new \RuntimeException('Could not remove an old avatar thumbnail');
			}
		}
	}

	// Keep the lock file in place so all writers synchronize on the same inode.
	private static function withAvatarLock(string $directory, callable $callback) {
		$lock = fopen($directory . '.avatar.lock', 'c');
		if ($lock === false) {
			throw new \RuntimeException('Could not open the avatar lock file');
		}
		try {
			if (!flock($lock, LOCK_EX)) {
				throw new \RuntimeException('Could not lock the avatar');
			}
			return $callback();
		} finally {
			fclose($lock);
		}
	}

}

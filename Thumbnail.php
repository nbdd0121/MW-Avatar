<?php
namespace Avatar;

class Thumbnail {

	private $image;
	public $width;
	public $height;
	public $type;

	private function __construct(\GdImage $image, array $imageInfo) {
		list($this->width, $this->height, $this->type) = $imageInfo;
		$this->image = $image;
	}

	public static function open($url, ?array $imageInfo = null): ?self {
		$imageInfo ??= @getimagesize($url);
		$image = match ($imageInfo[2] ?? null) {
			IMAGETYPE_GIF => @imagecreatefromgif($url),
			IMAGETYPE_PNG => @imagecreatefrompng($url),
			IMAGETYPE_JPEG => @imagecreatefromjpeg($url),
			default => false,
		};
		return $image ? new self($image, $imageInfo) : null;
	}

	public function createThumbnail($dimension, $file) {
		if ($dimension > $this->width) {
			$dimension = $this->width;
		}

		$thumb = imagecreatetruecolor($dimension, $dimension);
		imagesavealpha($thumb, true);
		$transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
		imagefill($thumb, 0, 0, $transparent);
		imagecopyresampled($thumb, $this->image, 0, 0, 0, 0, $dimension, $dimension, $this->width, $this->height);

		$tempFile = tempnam(dirname($file), '.avatar-');
		if ($tempFile === false) {
			throw new \RuntimeException('Could not create a temporary avatar file');
		}
		try {
			if (!imagepng($thumb, $tempFile) || !chmod($tempFile, 0644) || !rename($tempFile, $file)) {
				throw new \RuntimeException('Failed to save image ' . $file);
			}
		} finally {
			if (is_file($tempFile)) {
				unlink($tempFile);
			}
		}
	}
}

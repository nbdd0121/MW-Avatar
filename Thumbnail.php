<?php
namespace Avatar;

class Thumbnail {

	private $image;
	public $width;
	public $height;
	public $type;

	private function __construct($url) {
		$imageInfo = getimagesize($url);
		list($this->width, $this->height, $this->type) = $imageInfo;

		switch ($this->type) {
		case IMAGETYPE_GIF:
			$this->image = imagecreatefromgif($url);
			break;
		case IMAGETYPE_PNG:
			$this->image = imagecreatefrompng($url);
			break;
		case IMAGETYPE_JPEG:
			$this->image = imagecreatefromjpeg($url);
			break;
		}
	}

	public static function open($url) {
		return new self($url);
	}

	public function cleanup() {
		imagedestroy($this->image);
		$this->image = null;
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

		if (!imagepng($thumb, $file)) {
			throw new \Exception('Failed to save image ' . $file);
		}

		imagedestroy($thumb);
	}
}

<?php
namespace Avatar;

class AvatarProcessor {

	private $dataurl;
	private $image;
	public $width;
	public $height;
	public $type;

	public function __construct($dataurl) {
		$this->dataurl = $dataurl;

		$imageInfo = getimagesize($dataurl);
		list($this->width, $this->height, $this->type) = $imageInfo;
	}

	public function cleanup() {
		imagedestory($this->image);
		$this->image = null;
	}

	private function loadImage() {
		switch ($this->type) {
		case IMAGETYPE_GIF:
			$this->image = imagecreatefromgif($this->dataurl);
			break;
		case IMAGETYPE_PNG:
			$this->image = imagecreatefrompng($this->dataurl);
			break;
		case IMAGETYPE_JPEG:
			$this->image = imagecreatefromjpeg($this->dataurl);
			break;
		}
	}

	public function createThumbnail($dimension, $file) {
		if (!$this->image) {
			$this->loadImage();
		}
		if ($dimension > $this->width) {
			$dimension = $this->width;
		}

		$thumb = imagecreatetruecolor($dimension, $dimension);
		imagecopyresampled($thumb, $this->image, 0, 0, 0, 0, $dimension, $dimension, $this->width, $this->height);

		if (!imagepng($thumb, $file)) {
			throw new \Exception('Failed to save image ' . $file);
		}

		imagedestroy($thumb);
	}

}

class SpecialUpload extends \SpecialPage {

	public function __construct() {
		parent::__construct('AvatarUpload');
	}

	public function execute($par) {
		$this->requireLogin('prefsnologintext2');

		$this->setHeaders();
		$this->outputHeader();

		global $wgMaxAvatarResolution;
		$this->getOutput()->addJsConfigVars('wgMaxAvatarResolution', $wgMaxAvatarResolution);

		$this->getOutput()->addModules('ext.avatar.upload');

		$request = $this->getRequest();

		if ($request->wasPosted()) {
			if ($this->processUpload()) {
				$this->getOutput()->redirect(\SpecialPage::getTitleFor('Preferences')->getLinkURL());
			}
		} else {
			$this->displayMessage('');
		}
		$this->displayForm();
	}

	private function displayMessage($msg) {
		$this->getOutput()->addHTML(\Html::rawElement('div', array('class' => 'error', 'id' => 'errorMsg'), $msg));
	}

	private function processUpload() {
		$request = $this->getRequest();
		$dataurl = $request->getVal('avatar');
		if (!$dataurl || parse_url($dataurl, PHP_URL_SCHEME) !== 'data') {
			$this->displayMessage($this->msg('avatar-notuploaded'));
			return false;
		}

		$img = new AvatarProcessor($dataurl);

		global $wgMaxAvatarResolution;

		switch ($img->type) {
		case IMAGETYPE_GIF:
		case IMAGETYPE_PNG:
		case IMAGETYPE_JPEG:
			break;
		default:
			$this->displayMessage($this->msg('avatar-invalid'));
			return false;
		}

		// Must be square
		if ($img->width !== $img->height) {
			$this->displayMessage($this->msg('avatar-notsquare'));
			return false;
		}

		// Check if image is too small
		if ($img->width < 32 || $img->height < 32) {
			$this->displayMessage($this->msg('avatar-toosmall'));
			return false;
		}

		// Check if image is too big
		if ($img->width > $wgMaxAvatarResolution || $img->height > $wgMaxAvatarResolution) {
			$this->displayMessage($this->msg('avatar-toolarge'));
			return false;
		}

		// Avatar directories
		global $wgUploadDirectory;
		$uploadDir = $wgUploadDirectory . '/avatars/' . $this->getUser()->getId() . '/';
		mkdir($uploadDir, 0777, true);

		// We do this to convert format to png
		$img->createThumbnail($wgMaxAvatarResolution, $uploadDir . 'original.png');

		// Create thumbnails
		global $wgAvatarThumbRes;
		foreach ($wgAvatarThumbRes as $res) {
			$img->createThumbnail($res, $uploadDir . $res . '.png');
		}

		$this->displayMessage($this->msg('avatar-saved'));
		return true;
	}

	public function displayForm() {
		$html .= '<p></p>';
		$html .= \Html::hidden('avatar', '');
		$html .= \Html::hidden('title', $this->getTitle());

		$html .= \Xml::element('button', array('id' => 'pickfile'), $this->msg('avatarupload-selectfile'));

		$html .= ' ';

		// Submit button
		$html .= \Xml::submitButton($this->msg('avatarupload-submit')->text());

		// Wrap with a form
		$html = \Xml::tags('form', array('action' => $wgScript, 'method' => 'post'), $html);

		$this->getOutput()->addWikiMsg('clearyourcache');
		$this->getOutput()->addHTML($html);
	}

	public function isListed() {
		return false;
	}
}
<?php
namespace Avatar;

use MediaWiki\MediaWikiServices;

class UploadLogFormatter extends \LogFormatter {

	public function getActionLinks() {
		$user = $this->entry->getPerformerIdentity();
		$view = MediaWikiServices::getInstance()->getLinkRenderer()
			->makeKnownLink(\SpecialPage::getTitleFor('ViewAvatar'),
				$this->msg('logentry-avatar-action-view')->escaped(),
				[],
				['user' => $user->getName()]
			);
		return $this->msg('parentheses')->rawParams($view)->escaped();

	}

}

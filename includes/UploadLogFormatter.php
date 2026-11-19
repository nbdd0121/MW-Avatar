<?php
namespace Avatar;

use MediaWiki\Logging\LogFormatter;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPage;

class UploadLogFormatter extends LogFormatter {

	public function getActionLinks() {
		$user = $this->entry->getPerformerIdentity();
		$view = MediaWikiServices::getInstance()->getLinkRenderer()
			->makeKnownLink(SpecialPage::getTitleFor('ViewAvatar'),
				$this->msg('logentry-avatar-action-view')->text(),
				[],
				['user' => $user->getName()]
			);
		return $this->msg('parentheses')->rawParams($view)->escaped();

	}

}

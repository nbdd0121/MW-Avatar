<?php
namespace Avatar;
class UploadLogFormatter extends \LogFormatter {

	public function getActionLinks() {
		$user = $this->entry->getPerformer();
		$view = \Linker::linkKnown(
			\SpecialPage::getTitleFor('ViewAvatar'),
			$this->msg('logentry-avatar-action-view')->escaped(),
			array(),
			array(
				'user' => $user->getName(),
			)
		);
		return $this->msg('parentheses')->rawParams($view)->escaped();

	}

}
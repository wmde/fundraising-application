<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\UseCases\GetInTouch;

use WMDE\Fundraising\Frontend\MailAddress;
use WMDE\Fundraising\Frontend\Messenger;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchUseCase {

	private $messenger;
	/** @var GetInTouchRequest */
	private $request;

	public function __construct( Messenger $messenger ) {
		$this->messenger = $messenger;
	}

	public function processContact( GetInTouchRequest $request ): string {
		$this->request = $request;

		if ( $this->forwardContactRequest() && $this->confirmToUser() ) {
			return 'request successful';
		}
		return 'mail transmission failed';
	}

	private function forwardContactRequest(): bool {
		$this->messenger->sendMessage(
			$this->messenger->constructMessage(
				new MailAddress(
					$this->request->getEmailAddress(),
					implode( ' ', [ $this->request->getFirstName(), $this->request->getLastName() ] )
				),
				new MailAddress( 'kai.nissen@wikimedia.de' ),
				$this->request->getSubject(),
				$this->request->getMessageBody()
			)
		);

		return $this->messenger->getFailedRecipients() === [];
	}

	private function confirmToUser(): bool {
		$this->messenger->sendMessage(
			$this->messenger->constructMessage(
				new MailAddress( 'kai.nissen@wikimedia.de', 'Kai Nissen' ),
				new MailAddress( $this->request->getEmailAddress() ),
				'TODO',
				'fetch from CMW'
			)
		);

		return $this->messenger->getFailedRecipients() === [];
	}

}

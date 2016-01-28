<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\UseCases\GetInTouch;

use WMDE\Fundraising\Frontend\MailAddress;
use WMDE\Fundraising\Frontend\Messenger;
use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;
use WMDE\Fundraising\Frontend\Validation\GetInTouchValidator;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchUseCase {

	private $validator;
	private $messenger;
	/** @var GetInTouchRequest */
	private $request;

	public function __construct( GetInTouchValidator $validator, Messenger $messenger ) {
		$this->validator = $validator;
		$this->messenger = $messenger;
	}

	public function processContact( GetInTouchRequest $request ): ValidationResponse {
		$this->request = $request;

		if ( !$this->validator->validate( $request ) ) {
			return ValidationResponse::newFailureResponse( $this->validator->getConstraintViolations() );
		}

		$this->forwardContactRequest();
		$this->confirmToUser();
		return ValidationResponse::newSuccessResponse();
	}

	private function forwardContactRequest() {
		$this->messenger->sendMessage(
			$this->request->getSubject(),
			$this->request->getMessageBody(),
			new MailAddress( 'kai.nissen@wikimedia.de' ),
			new MailAddress(
				$this->request->getEmailAddress(),
				implode( ' ', [ $this->request->getFirstName(), $this->request->getLastName() ] )
			)
		);
	}

	private function confirmToUser() {
		$this->messenger->sendMessage(
			'TODO',
			'fetch from CMW',
			new MailAddress( $this->request->getEmailAddress() )
		);
	}

}

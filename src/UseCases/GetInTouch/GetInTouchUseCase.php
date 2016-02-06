<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\UseCases\GetInTouch;

use WMDE\Fundraising\Frontend\MailAddress;
use WMDE\Fundraising\Frontend\Messenger;
use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;
use WMDE\Fundraising\Frontend\SimpleMessage;
use WMDE\Fundraising\Frontend\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Validation\GetInTouchValidator;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchUseCase {

	private $validator;
	private $messenger;
	private $templateBasedMailer;

	public function __construct( GetInTouchValidator $validator, Messenger $messenger, TemplateBasedMailer $templateMailer ) {
		$this->validator = $validator;
		$this->messenger = $messenger;
		$this->templateBasedMailer = $templateMailer;
	}

	/**
	 * @throws \RuntimeException
	 */
	public function processContactRequest( GetInTouchRequest $request ): ValidationResponse {
		if ( !$this->validator->validate( $request ) ) {
			return ValidationResponse::newFailureResponse( $this->validator->getConstraintViolations() );
		}

		$this->forwardContactRequest( $request );
		$this->confirmToUser( $request );

		return ValidationResponse::newSuccessResponse();
	}

	private function forwardContactRequest( GetInTouchRequest $request ) {
		$this->messenger->sendMessageToOperator(
			new SimpleMessage(
				$request->getSubject(),
				$request->getMessageBody()
			),
			new MailAddress( $request->getEmailAddress() )
		);
	}

	private function confirmToUser( GetInTouchRequest $request ) {
		$this->templateBasedMailer->sendMail( new MailAddress( $request->getEmailAddress() ) );
	}

}

<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\GetInTouch;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\GetInTouchMailerInterface;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\OperatorMailer;
use WMDE\Fundraising\Frontend\Validation\GetInTouchValidator;
use WMDE\FunValidators\ValidationResponse;

/**
 * @license GNU GPL v2+
 */
class GetInTouchUseCase {

	private $validator;
	private $operatorMailer;
	private $userMailer;

	public function __construct( GetInTouchValidator $validator, OperatorMailer $operatorMailer,
		GetInTouchMailerInterface $userMailer ) {

		$this->validator = $validator;
		$this->operatorMailer = $operatorMailer;
		$this->userMailer = $userMailer;
	}

	/**
	 * @throws \RuntimeException
	 */
	public function processContactRequest( GetInTouchRequest $request ): ValidationResponse {
		$validationResult = $this->validator->validate( $request );

		if ( $validationResult->hasViolations() ) {
			return ValidationResponse::newFailureResponse( $validationResult->getViolations() );
		}

		$this->sendContactRequestToOperator( $request );
		$this->sendNotificationToUser( $request );

		return ValidationResponse::newSuccessResponse();
	}

	private function sendContactRequestToOperator( GetInTouchRequest $request ): void {
		$this->operatorMailer->sendMailToOperator(
			new EmailAddress( $request->getEmailAddress() ),
			$request->getSubject(),
			$this->getTemplateParams( $request )
		);
	}

	private function sendNotificationToUser( GetInTouchRequest $request ): void {
		// We don't send any template input here to avoid misusing the form for spam.
		// The user just gets a "We received your inquiry and will contact you shortly" message
		$this->userMailer->sendMail( new EmailAddress( $request->getEmailAddress() ) );
	}

	private function getTemplateParams( GetInTouchRequest $request ): array {
		return [
			'firstName' => $request->getFirstName(),
			'lastName' => $request->getLastName(),
			'emailAddress' => $request->getEmailAddress(),
			'donationNumber' => $request->getDonationNumber(),
			'subject' => $request->getSubject(),
			'category' => $request->getCategory(),
			'message' => $request->getMessageBody()
		];
	}

}

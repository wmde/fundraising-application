<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\GetInTouch;

use WMDE\Fundraising\Frontend\Infrastructure\OperatorMailer;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\Validation\ValidationResponse;
use WMDE\Fundraising\Frontend\Validation\GetInTouchValidator;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchUseCase {

	private $validator;
	private $operatorMailer;
	private $userMailer;

	public function __construct( GetInTouchValidator $validator, OperatorMailer $operatorMailer,
								 TemplateBasedMailer $userMailer ) {
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

	private function sendContactRequestToOperator( GetInTouchRequest $request ) {
		$this->operatorMailer->sendMailToOperator(
			new EmailAddress( $request->getEmailAddress() ),
			$this->getTemplateParams( $request )
		);
	}

	private function sendNotificationToUser( GetInTouchRequest $request ) {
		// We don't send any template input here to avoid misusing the form for spam.
		// The user just gets a "We received your inquiry and will contact you shortly" message
		$this->userMailer->sendMail( new EmailAddress( $request->getEmailAddress() ) );
	}

	private function getTemplateParams( GetInTouchRequest $request ) {
		return [
			'firstName' => $request->getFirstName(),
			'lastName' => $request->getLastName(),
			'emailAddress' => $request->getEmailAddress(),
			'subject' => $request->getSubject(),
			'message' => $request->getMessageBody()
		];
	}

}

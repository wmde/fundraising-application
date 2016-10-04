<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\ApplicationContext\UseCases\GetInTouch;

use WMDE\Fundraising\Frontend\Infrastructure\Message;
use WMDE\Fundraising\Frontend\Infrastructure\Messenger;
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
	private $forwardingMailer;
	private $confirmingMailer;

	public function __construct( GetInTouchValidator $validator, TemplateBasedMailer $forwardingMailer,
								 TemplateBasedMailer $confirmingMailer ) {
		$this->validator = $validator;
		$this->forwardingMailer = $forwardingMailer;
		$this->confirmingMailer = $confirmingMailer;
	}

	/**
	 * @throws \RuntimeException
	 */
	public function processContactRequest( GetInTouchRequest $request ): ValidationResponse {
		$validationResult = $this->validator->validate( $request );

		if ( $validationResult->hasViolations() ) {
			return ValidationResponse::newFailureResponse( $validationResult->getViolations() );
		}

		$this->forwardContactRequest( $request );
		$this->confirmToUser( $request );

		return ValidationResponse::newSuccessResponse();
	}

	private function forwardContactRequest( GetInTouchRequest $request ) {
		$this->forwardingMailer->sendMailToOperator(
			new EmailAddress( $request->getEmailAddress() ),
			$this->getTemplateParams( $request )
		);
	}

	private function confirmToUser( GetInTouchRequest $request ) {
		$this->confirmingMailer->sendMail( new EmailAddress( $request->getEmailAddress() ) );
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

<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\PaymentContext\Domain\BankDataGenerator;
use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;

class MembershipFormViolationPresenter {

	public function __construct(
		private readonly TwigTemplate $template,
		private readonly BankDataGenerator $bankDataGenerator ) {
	}

	public function present( ApplyForMembershipRequest $request, bool $showMembershipTypeOption ): string {
		return $this->template->render(
			[
				'initialFormValues' => $this->getMembershipFormArguments( $request ),
				'showMembershipTypeOption' => $showMembershipTypeOption ? 'true' : 'false'
			]
		);
	}

	private function getMembershipFormArguments( ApplyForMembershipRequest $request ): array {
		$paymentRequest = $request->getPaymentCreationRequest();
		$bankData = $this->bankDataGenerator->getBankDataFromIban( new Iban( $paymentRequest->iban ) );

		return [
			'addressType' => $request->isCompanyApplication() ? 'firma' : 'person',
			'salutation' => $request->getApplicantSalutation(),
			'title' => $request->getApplicantTitle(),
			'firstName' => $request->getApplicantFirstName(),
			'lastName' => $request->getApplicantLastName(),
			'companyName' => $request->getApplicantCompanyName(),
			'street' => $request->getApplicantStreetAddress(),
			'postcode' => $request->getApplicantPostalCode(),
			'city' => $request->getApplicantCity(),
			'country' => $request->getApplicantCountryCode(),
			'email' => $request->getApplicantEmailAddress(),
			'iban' => $request->getPaymentCreationRequest()->iban,
			'bic' => $request->getPaymentCreationRequest()->bic,
			'accountNumber' => $bankData->account,
			'bankCode' => $bankData->bankCode,
			'bankname' => $bankData->bankName
		];
	}

}

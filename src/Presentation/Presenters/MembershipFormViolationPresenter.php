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
		$paymentParameters = $request->paymentParameters;
		$bankData = $this->bankDataGenerator->getBankDataFromIban( new Iban( $paymentParameters->iban ) );

		return [
			'addressType' => $request->isCompanyApplication() ? 'firma' : 'person',
			'salutation' => $request->applicantSalutation,
			'title' => $request->applicantTitle,
			'firstName' => $request->applicantFirstName,
			'lastName' => $request->applicantLastName,
			'companyName' => $request->applicantCompanyName,
			'street' => $request->applicantStreetAddress,
			'postcode' => $request->applicantPostalCode,
			'city' => $request->applicantCity,
			'country' => $request->applicantCountryCode,
			'email' => $request->applicantEmailAddress,
			'iban' => $paymentParameters->iban,
			'bic' => $paymentParameters->bic,
			'accountNumber' => $bankData->account,
			'bankCode' => $bankData->bankCode,
			'bankname' => $bankData->bankName
		];
	}

}

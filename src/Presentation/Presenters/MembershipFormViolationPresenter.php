<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MembershipFormViolationPresenter {

	private $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function present( ApplyForMembershipRequest $request, bool $showMembershipTypeOption ): string {
		return $this->template->render(
			[
				'initialFormValues' => $this->getMembershipFormArguments( $request ),
				'showMembershipTypeOption' => $showMembershipTypeOption ? 'true' : 'false'
			]
		);
	}

	private function getMembershipFormArguments( ApplyForMembershipRequest $request ) {
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
			'iban' => $request->getPaymentBankData()->getIban()->toString(),
			'bic' => $request->getPaymentBankData()->getBic(),
			'accountNumber' => $request->getPaymentBankData()->getAccount(),
			'bankCode' => $request->getPaymentBankData()->getBankCode(),
			'bankname' => $request->getPaymentBankData()->getBankName()
		];
	}

}

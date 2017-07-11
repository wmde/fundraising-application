<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipRequest;
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

	private function getMembershipFormArguments( ApplyForMembershipRequest $request ): array {
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
			'iban' => $request->getBankData()->getIban()->toString(),
			'bic' => $request->getBankData()->getBic(),
			'accountNumber' => $request->getBankData()->getAccount(),
			'bankCode' => $request->getBankData()->getBankCode(),
			'bankname' => $request->getBankData()->getBankName()
		];
	}

}

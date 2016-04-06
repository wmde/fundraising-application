<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Domain\Model\PersonalInfo;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationRequest;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationFormViolationPresenter {

	private $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function present( AddDonationRequest $request ): string {
		return $this->template->render( $this->getDonationFormArguments( $request ) );
	}

	private function getDonationFormArguments( AddDonationRequest $request ) {
		return array_merge(
			[
				'betrag' => $request->getAmount(),
				'zahlweise' => $request->getPaymentType(),
				'periode' => $request->getInterval(),
				'iban' => $request->getIban(),
				'bic' => $request->getBic(),
				'bankName' => $request->getBankName()
			],
			$this->getPersonalInfo( $request->getPersonalInfo() )
		);
	}

	private function getPersonalInfo( PersonalInfo $personalInfo = null ) {
		if ( $personalInfo === null ) {
			return [];
		}

		return array_merge(
			$this->getPersonName( $personalInfo->getPersonName() ),
			$this->getPhysicalAddress( $personalInfo->getPhysicalAddress() ),
			[ 'email' => $personalInfo->getEmailAddress() ]
		);
	}

	private function getPersonName( PersonName $personName ) {
		return [
			'adresstyp' => $personName->getPersonType(),
			'anrede' => $personName->getSalutation(),
			'titel' => $personName->getTitle(),
			'firma' => $personName->getCompanyName(),
			'vorname' => $personName->getFirstName(),
			'nachname' => $personName->getLastName(),
		];
	}

	private function getPhysicalAddress( PhysicalAddress $address ) {
		return [
			'strasse' => $address->getStreetAddress(),
			'plz' => $address->getPostalCode(),
			'ort' => $address->getCity(),
			'country' => $address->getCountryCode(),
		];
	}

}

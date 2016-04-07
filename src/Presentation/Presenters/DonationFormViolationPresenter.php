<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationRequest;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationFormViolationPresenter {

	private $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	/**
	 * @param ConstraintViolation[] $violations
	 * @param AddDonationRequest $request
	 * @return string
	 */
	public function present( array $violations, AddDonationRequest $request ): string {
		return $this->template->render(
			array_merge(
				$this->getDonationFormArguments( $request ),
				[ 'violatedFields' => $this->getViolatedFields( $violations ) ]
			)
		);
	}

	private function getDonationFormArguments( AddDonationRequest $request ) {
		return array_merge(
			[
				'betrag' => $request->getAmount()->getEuroString(),
				'zahlweise' => $request->getPaymentType(),
				'periode' => $request->getInterval(),
				'iban' => $request->getIban(),
				'bic' => $request->getBic(),
				'bankName' => $request->getBankName()
			],
			$this->getPersonalInfo( $request->getPersonalInfo() )
		);
	}

	private function getPersonalInfo( Donor $personalInfo = null ) {
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

	/**
	 * @param ConstraintViolation[] $violations
	 * @return array
	 */
	private function getViolatedFields( array $violations ) {
		$fieldNames = [];
		foreach ( $violations as $violation ) {
			$fieldNames[$violation->getSource()] = $violation->getMessageIdentifier();
		}

		return $fieldNames;
	}

}

<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\Presentation\AmountFormatter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationRequest;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class DonationFormViolationPresenter {

	private $template;
	private $amountFormatter;

	public function __construct( TwigTemplate $template, AmountFormatter $amountFormatter ) {
		$this->template = $template;
		$this->amountFormatter = $amountFormatter;
	}

	/**
	 * @param ConstraintViolation[] $violations
	 * @param AddDonationRequest $request
	 * @return string
	 */
	public function present( array $violations, AddDonationRequest $request ): string {
		return $this->template->render(
			[
				'initialFormValues' => $this->getDonationFormArguments( $request ),
				'violatedFields' => $this->getViolatedFields( $violations )
			]
		);
	}

	private function getDonationFormArguments( AddDonationRequest $request ) {
		return array_merge(
			[
				'amount' => $this->amountFormatter->format( $request->getAmount() ),
				'paymentType' => $request->getPaymentType(),
				'paymentIntervalInMonths' => $request->getInterval(),
				'iban' => $request->getIban(),
				'bic' => $request->getBic(),
				'bankName' => $request->getBankName()
			],
			$this->getPersonalInfo( $request->getDonor() )
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
			'addressType' => $personName->getPersonType(),
			'salutation' => $personName->getSalutation(),
			'title' => $personName->getTitle(),
			'company' => $personName->getCompanyName(),
			'firstName' => $personName->getFirstName(),
			'lastName' => $personName->getLastName(),
		];
	}

	private function getPhysicalAddress( PhysicalAddress $address ) {
		return [
			'street' => $address->getStreetAddress(),
			'postcode' => $address->getPostalCode(),
			'city' => $address->getCity(),
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

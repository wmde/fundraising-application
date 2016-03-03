<?php

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * Render the confirmation pages for donations
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AddDonationHtmlPresenter {

	private $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function present( Donation $donation ): string {
		return $this->template->render( $this->getConfirmationPageArguments( $donation ) );
	}

	private function getConfirmationPageArguments( Donation $donation ) {
		return array_merge( [
			'donation' => [
				'id' => $donation->getId(),
				'status' => $donation->getStatus(),
				'amount' => $donation->getAmount(),
				'interval' => $donation->getInterval(),
				'paymentType' => $donation->getPaymentType(),
				'optsIntoNewsletter' => $donation->getOptsIntoNewsletter(),
				'bankTransferCode' => $donation->getBankTransferCode(),
				// TODO: use locale to determine the date format
				'creationDate' => ( new \DateTime() )->format( 'd.M.Y' ),
				// TODO: set cookie duration for "hide banner cookie"
				'cookieDuration' => ''
			],
			$this->getPersonArguments( $donation ),
			$this->getBankDataArguments( $donation )
		] );
	}

	private function getPersonArguments( Donation $donation ): array {
		if ( $donation->getPersonalInfo() !== null ) {
			return [
				'person' => [
					'fullName' => $donation->getPersonalInfo()->getPersonName()->getFullName(),
					'streetAddress' => $donation->getPersonalInfo()->getPhysicalAddress()->getStreetAddress(),
					'postalCode' => $donation->getPersonalInfo()->getPhysicalAddress()->getPostalCode(),
					'city' => $donation->getPersonalInfo()->getPhysicalAddress()->getCity(),
					'email' => $donation->getPersonalInfo()->getEmailAddress(),
				]
			];
		}

		return [];
	}

	private function getBankDataArguments( Donation $donation ): array {
		if ( $donation->getBankData() !== null ) {
			return [
				'bankData' => [
					'iban' => $donation->getBankData()->getIban()->toString(),
					'bic' => $donation->getBankData()->getBankCode(),
					'bankname' => $donation->getBankData()->getBankName(),
				]
			];
		}

		return [];
	}

}
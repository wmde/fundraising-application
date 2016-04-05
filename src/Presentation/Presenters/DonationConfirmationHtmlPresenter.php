<?php

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Presentation\SelectedConfirmationPage;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * Render the confirmation pages for donations
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationConfirmationHtmlPresenter {

	private $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function present( Donation $donation, string $updateToken, SelectedConfirmationPage $selectedPage ): string {
		return $this->template->render( $this->getConfirmationPageArguments( $donation, $updateToken, $selectedPage ) );
	}

	private function getConfirmationPageArguments( Donation $donation, string $updateToken,
												   SelectedConfirmationPage $selectedPage ) {

		return array_merge( [
			'templateCampaign' => $selectedPage->getCampaignCode(),
			'templateName' => $selectedPage->getPageTitle(),
			'donation' => [
				'id' => $donation->getId(),
				'status' => $donation->getStatus(),
				'amount' => $donation->getAmount()->getEuroFloat(),
				'interval' => $donation->getInterval(),
				'paymentType' => $donation->getPaymentType(),
				'optsIntoNewsletter' => $donation->getOptsIntoNewsletter(),
				'bankTransferCode' => $donation->getBankTransferCode(),
				// TODO: use locale to determine the date format
				'creationDate' => ( new \DateTime() )->format( 'd.m.Y' ),
				// TODO: set cookie duration for "hide banner cookie"
				'cookieDuration' => '',
				'updateToken' => $updateToken
			],
			'person' => $this->getPersonArguments( $donation ),
			'bankData' => $this->getBankDataArguments( $donation )
		] );
	}

	private function getPersonArguments( Donation $donation ): array {
		if ( $donation->getPersonalInfo() !== null ) {
			return [
				'fullName' => $donation->getPersonalInfo()->getPersonName()->getFullName(),
				'streetAddress' => $donation->getPersonalInfo()->getPhysicalAddress()->getStreetAddress(),
				'postalCode' => $donation->getPersonalInfo()->getPhysicalAddress()->getPostalCode(),
				'city' => $donation->getPersonalInfo()->getPhysicalAddress()->getCity(),
				'email' => $donation->getPersonalInfo()->getEmailAddress(),
			];
		}

		return [];
	}

	private function getBankDataArguments( Donation $donation ): array {
		if ( $donation->getBankData() !== null ) {
			return [
				'iban' => $donation->getBankData()->getIban()->toString(),
				'bic' => $donation->getBankData()->getBic(),
				'bankname' => $donation->getBankData()->getBankName(),
			];
		}

		return [];
	}

}
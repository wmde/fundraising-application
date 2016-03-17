<?php

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationResponse;

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

	public function present( AddDonationResponse $responseModel ): string {
		return $this->template->render( $this->getConfirmationPageArguments( $responseModel ) );
	}

	private function getConfirmationPageArguments( AddDonationResponse $responseModel ) {
		return array_merge( [
			'donation' => [
				'id' => $responseModel->getDonation()->getId(),
				'status' => $responseModel->getDonation()->getStatus(),
				'amount' => $responseModel->getDonation()->getAmount(),
				'interval' => $responseModel->getDonation()->getInterval(),
				'paymentType' => $responseModel->getDonation()->getPaymentType(),
				'optsIntoNewsletter' => $responseModel->getDonation()->getOptsIntoNewsletter(),
				'bankTransferCode' => $responseModel->getDonation()->getBankTransferCode(),
				// TODO: use locale to determine the date format
				'creationDate' => ( new \DateTime() )->format( 'd.m.Y' ),
				// TODO: set cookie duration for "hide banner cookie"
				'cookieDuration' => '',
				'updateToken' => $responseModel->getUpdateToken()
			],
			'person' => $this->getPersonArguments( $responseModel->getDonation() ),
			'bankData' => $this->getBankDataArguments( $responseModel->getDonation() )
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
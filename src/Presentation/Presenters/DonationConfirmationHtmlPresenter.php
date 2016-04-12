<?php

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\Frontend\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentMethod;
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
				'amount' => $donation->getAmount()->getEuroFloat(), // TODO: getEuroString might be better
				'interval' => $donation->getPaymentIntervalInMonths(),
				'paymentType' => $donation->getPaymentType(),
				'optsIntoNewsletter' => $donation->getOptsIntoNewsletter(),
				'bankTransferCode' => $this->getBankTransferCode( $donation->getPaymentMethod() ),
				// TODO: use locale to determine the date format
				'creationDate' => ( new \DateTime() )->format( 'd.m.Y' ),
				// TODO: set cookie duration for "hide banner cookie"
				'cookieDuration' => '',
				'updateToken' => $updateToken
			],
			'person' => $this->getPersonArguments( $donation ),
			'bankData' => $this->getBankDataArguments( $donation->getPaymentMethod() )
		] );
	}

	private function getPersonArguments( Donation $donation ): array {
		if ( $donation->getDonor() !== null ) {
			return [
				'fullName' => $donation->getDonor()->getPersonName()->getFullName(),
				'streetAddress' => $donation->getDonor()->getPhysicalAddress()->getStreetAddress(),
				'postalCode' => $donation->getDonor()->getPhysicalAddress()->getPostalCode(),
				'city' => $donation->getDonor()->getPhysicalAddress()->getCity(),
				'email' => $donation->getDonor()->getEmailAddress(),
			];
		}

		return [];
	}

	private function getBankTransferCode( PaymentMethod $paymentMethod ): string {
		if ( $paymentMethod instanceof BankTransferPayment ) {
			return $paymentMethod->getBankTransferCode();
		}

		return '';
	}

	private function getBankDataArguments( PaymentMethod $paymentMethod ): array {
		if ( $paymentMethod instanceof DirectDebitPayment ) {
			return [
				'iban' => $paymentMethod->getBankData()->getIban()->toString(),
				'bic' => $paymentMethod->getBankData()->getBic(),
				'bankname' => $paymentMethod->getBankData()->getBankName(),
			];
		}

		return [];
	}

}
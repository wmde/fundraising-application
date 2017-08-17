<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\Infrastructure\PiwikEvents;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentMethod;
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

	public function present( Donation $donation, string $updateToken, SelectedConfirmationPage $selectedPage,
							 PiwikEvents $piwikEvents, int $membershipFeePaymentDelay ): string {
		return $this->template->render(
			$this->getConfirmationPageArguments(
				$donation, $updateToken, $selectedPage, $piwikEvents, $membershipFeePaymentDelay
			)
		);
	}

	private function getConfirmationPageArguments( Donation $donation, string $updateToken,
												   SelectedConfirmationPage $selectedPage, PiwikEvents $piwikEvents,
												   int $membershipFeePaymentDelay ): array {

		return [
			'main_template' => $selectedPage->getPageTitle(),
			'templateCampaign' => $selectedPage->getCampaignCode(),
			'donation' => [
				'id' => $donation->getId(),
				'status' => $this->mapStatus( $donation->getStatus() ),
				'amount' => $donation->getAmount()->getEuroFloat(),
				'interval' => $donation->getPaymentIntervalInMonths(),
				'paymentType' => $donation->getPaymentType(),
				'optsIntoNewsletter' => $donation->getOptsIntoNewsletter(),
				'bankTransferCode' => $this->getBankTransferCode( $donation->getPaymentMethod() ),
				// TODO: use locale to determine the date format
				'creationDate' => ( new \DateTime() )->format( 'd.m.Y' ),
				// TODO: set cookie duration for "hide banner cookie"
				'cookieDuration' => '15552000', // 180 days
				'updateToken' => $updateToken
			],
			'person' => $this->getPersonArguments( $donation ),
			'bankData' => $this->getBankDataArguments( $donation->getPaymentMethod() ),
			'initialFormValues' => $this->getInitialMembershipFormValues( $donation ),
			'piwikEvents' => $piwikEvents->getEvents(),
			'delay_in_days' => $membershipFeePaymentDelay
		];
	}

	private function getPersonArguments( Donation $donation ): array {
		if ( $donation->getDonor() !== null ) {
			return [
				'salutation' => $donation->getDonor()->getName()->getSalutation(),
				'fullName' => $donation->getDonor()->getName()->getFullName(),
				'firstName' => $donation->getDonor()->getName()->getFirstName(),
				'lastName' => $donation->getDonor()->getName()->getLastName(),
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

	private function getInitialMembershipFormValues( Donation $donation ): array {
		return array_merge(
			$this->getMembershipFormPersonValues( $donation->getDonor() ),
			$this->getMembershipFormBankDataValues( $donation->getPaymentMethod() )
		);
	}

	private function getMembershipFormPersonValues( Donor $donor = null ): array {
		if ( $donor === null ) {
			return [];
		}

		return [
			'addressType' => $donor->getName()->getPersonType(),
			'salutation' => $donor->getName()->getSalutation(),
			'title' => $donor->getName()->getTitle(),
			'firstName' => $donor->getName()->getFirstName(),
			'lastName' => $donor->getName()->getLastName(),
			'companyName' => $donor->getName()->getCompanyName(),
			'street' => $donor->getPhysicalAddress()->getStreetAddress(),
			'postcode' => $donor->getPhysicalAddress()->getPostalCode(),
			'city' => $donor->getPhysicalAddress()->getCity(),
			'country' => $donor->getPhysicalAddress()->getCountryCode(),
			'email' => $donor->getEmailAddress(),
		];
	}

	private function getMembershipFormBankDataValues( PaymentMethod $paymentMethod ): array {
		if ( !$paymentMethod instanceof DirectDebitPayment ) {
			return [];
		}

		return [
			'iban' => $paymentMethod->getBankData()->getIban()->toString(),
			'bic' => $paymentMethod->getBankData()->getBic(),
			'accountNumber' => $paymentMethod->getBankData()->getAccount(),
			'bankCode' => $paymentMethod->getBankData()->getBankCode(),
			'bankname' => $paymentMethod->getBankData()->getBankName(),
		];
	}

	/**
	 * Maps the membership application's status to a translatable message key
	 *
	 * @param string $status
	 * @return string
	 */
	private function mapStatus( string $status ): string {
		switch ( $status ) {
			case Donation::STATUS_MODERATION:
				return 'status-pending';
			case Donation::STATUS_NEW:
				return 'status-new';
			case Donation::STATUS_EXTERNAL_INCOMPLETE:
				return 'status-unconfirmed';
			case Donation::STATUS_PROMISE:
				return 'status-pledge';
			case Donation::STATUS_EXTERNAL_BOOKED:
				return 'status-booked';
			default:
				return 'status-unknown';
		}
	}
}

<?php

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\Frontend\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\Donor;
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

		return [
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
			'bankData' => $this->getBankDataArguments( $donation->getPaymentMethod() ),
			'initialFormValues' => $this->getInitialMembershipFormValues( $donation )
		];
	}

	private function getPersonArguments( Donation $donation ): array {
		if ( $donation->getDonor() !== null ) {
			return [
				'salutation' => $donation->getDonor()->getPersonName()->getSalutation(),
				'fullName' => $donation->getDonor()->getPersonName()->getFullName(),
				'firstName' => $donation->getDonor()->getPersonName()->getFirstName(),
				'lastName' => $donation->getDonor()->getPersonName()->getLastName(),
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

	private function getInitialMembershipFormValues( Donation $donation ) {
		return array_merge(
			$this->getMembershipFormPersonValues( $donation->getDonor() ),
			$this->getMembershipFormBankDataValues( $donation->getPaymentMethod() )
		);
	}

	/**
	 * @param Donor|null $donor
	 * @return array
	 */
	private function getMembershipFormPersonValues( $donor ) {
		if ( $donor === null ) {
			return [];
		}

		return [
			'addressType' => $donor->getPersonName()->getPersonType(),
			'salutation' => $donor->getPersonName()->getSalutation(),
			'title' => $donor->getPersonName()->getTitle(),
			'firstName' => $donor->getPersonName()->getFirstName(),
			'lastName' => $donor->getPersonName()->getLastName(),
			'companyName' => $donor->getPersonName()->getCompanyName(),
			'street' => $donor->getPhysicalAddress()->getStreetAddress(),
			'postcode' => $donor->getPhysicalAddress()->getPostalCode(),
			'city' => $donor->getPhysicalAddress()->getCity(),
			'country' => $donor->getPhysicalAddress()->getCountryCode(),
			'email' => $donor->getEmailAddress(),
		];
	}

	private function getMembershipFormBankDataValues( PaymentMethod $paymentMethod ) {
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

}

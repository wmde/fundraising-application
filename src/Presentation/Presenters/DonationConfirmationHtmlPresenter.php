<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Infrastructure\PiwikEvents;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentMethod;
use WMDE\Fundraising\Frontend\Presentation\DonationMembershipApplicationAdapter;
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
	private $donationMembershipApplicationAdapter;
	private $urlGenerator;

	public function __construct( TwigTemplate $template, UrlGenerator $urlGenerator ) {
		$this->template = $template;
		$this->urlGenerator = $urlGenerator;
		$this->donationMembershipApplicationAdapter = new DonationMembershipApplicationAdapter();
	}

	public function present( Donation $donation, string $updateToken, string $accessToken,
							 SelectedConfirmationPage $selectedPage, PiwikEvents $piwikEvents ): string {
		return $this->template->render(
			$this->getConfirmationPageArguments( $donation, $updateToken, $accessToken, $selectedPage, $piwikEvents )
		);
	}

	private function getConfirmationPageArguments( Donation $donation, string $updateToken, string $accessToken,
												   SelectedConfirmationPage $selectedPage, PiwikEvents $piwikEvents ): array {

		return [
			'template_name' => $selectedPage->getPageTitle(),
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
				'updateToken' => $updateToken,
				'accessToken' => $accessToken
			],
			'address' => $this->getAddressArguments( $donation ),
			'bankData' => $this->getBankDataArguments( $donation->getPaymentMethod() ),
			'initialFormValues' => $this->donationMembershipApplicationAdapter->getInitialMembershipFormValues( $donation ),
			'piwikEvents' => $piwikEvents->getEvents(),
			'commentUrl' => $this->urlGenerator->generateUrl(
				'AddCommentPage',
				[
					'donationId' => $donation->getId(),
					'updateToken' => $updateToken,
					'accessToken' =>$accessToken
				]
			)
		];
	}

	private function getAddressArguments( Donation $donation ): array {
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
			case Donation::STATUS_CANCELLED:
				return 'status-canceled';
			default:
				return 'status-unknown';
		}
	}
}

<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use DateTime;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Applicant;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\Frontend\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationResponse;
use WMDE\Fundraising\Frontend\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationPresenter;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentMethod;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * Render the confirmation pages for membership applications
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MembershipApplicationConfirmationHtmlPresenter implements ShowApplicationConfirmationPresenter {

	private $template;
	private $html = '';

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function presentResponseModel( ShowApplicationConfirmationResponse $response ): void {
		$this->html = $this->template->render(
			$this->getConfirmationPageArguments(
				$response->getApplication(),
				$response->getUpdateToken()
			)
		);
	}

	public function getHtml(): string {
		return $this->html;
	}

	private function getConfirmationPageArguments( Application $membershipApplication, string $updateToken ): array {
		return [
			'membershipApplication' => $this->getApplicationArguments( $membershipApplication, $updateToken ),
			'person' => $this->getPersonArguments( $membershipApplication->getApplicant() ),
			'bankData' => $this->getBankDataArguments( $membershipApplication->getPayment()->getPaymentMethod() ),
			'payPalData' => $this->getPayPalDataArguments(
				$membershipApplication->getPayment()->getPaymentMethod()
			)
		];
	}

	private function getApplicationArguments( Application $membershipApplication, string $updateToken ): array {
		return [
			'id' => $membershipApplication->getId(),
			'membershipType' => $membershipApplication->getType(),
			'paymentType' => $membershipApplication->getPayment()->getPaymentMethod()->getType(),
			'status' => $this->mapStatus( $membershipApplication->isConfirmed() ),
			'membershipFee' => $membershipApplication->getPayment()->getAmount()->getEuroString(),
			'paymentIntervalInMonths' => $membershipApplication->getPayment()->getIntervalInMonths(),
			'updateToken' => $updateToken
		];
	}

	private function getPersonArguments( Applicant $applicant ): array {
		return [
			'salutation' => $applicant->getName()->getSalutation(),
			'title' => $applicant->getName()->getTitle(),
			'fullName' => $applicant->getName()->getFullName(),
			'streetAddress' => $applicant->getPhysicalAddress()->getStreetAddress(),
			'postalCode' => $applicant->getPhysicalAddress()->getPostalCode(),
			'city' => $applicant->getPhysicalAddress()->getCity(),
			'email' => $applicant->getEmailAddress(),
		];
	}

	private function getBankDataArguments( PaymentMethod $payment ): array {
		if ( $payment instanceof DirectDebitPayment ) {
			return [
				'iban' => $payment->getBankData()->getIban()->toString(),
				'bic' => $payment->getBankData()->getBic(),
				'bankName' => $payment->getBankData()->getBankName(),
			];
		}

		return [];
	}

	private function getPayPalDataArguments( PaymentMethod $payment ): array {
		if ( $payment instanceof PayPalPayment ) {
			return [
				'firstPaymentDate' => ( new DateTime( $payment->getPayPalData()->getFirstPaymentDate() ) )->format( 'd.m.Y' )
			];
		}

		return [];
	}

	/**
	 * Maps the membership application's status to a translatable message key
	 *
	 * @param bool $isConfirmed
	 * @return string
	 */
	private function mapStatus( bool $isConfirmed ): string {
		return $isConfirmed ? 'status-booked' : 'status-unconfirmed';
	}

	public function presentApplicationWasPurged(): void {
		$this->html = 'Membership application was purged'; // TODO
	}

	public function presentAccessViolation(): void {
		$this->html = 'ACCESS VIOLATION'; // TODO
	}

}
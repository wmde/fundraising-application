<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use DateTime;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\MembershipContext\Domain\Model\Applicant;
use WMDE\Fundraising\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationPresenter;
use WMDE\Fundraising\PaymentContext\Domain\BankDataGenerator;
use WMDE\Fundraising\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentMethod;
use WMDE\Fundraising\PaymentContext\Domain\Model\PayPalPayment;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MembershipApplicationConfirmationHtmlPresenter implements ShowApplicationConfirmationPresenter {

	private $template;
	private $bankDataGenerator;
	private $html = '';

	/**
	 * @var \Exception|null
	 */
	private $exception = null;

	public function __construct( TwigTemplate $template, BankDataGenerator $bankDataGenerator ) {
		$this->template = $template;
		$this->bankDataGenerator = $bankDataGenerator;
	}

	public function presentConfirmation( Application $application, string $updateToken ): void {
		$this->html = $this->template->render(
			$this->getConfirmationPageArguments(
				$application,
				$updateToken
			)
		);
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function getHtml(): string {
		if ( $this->exception !== null ) {
			throw $this->exception;
		}

		return $this->html;
	}

	private function getConfirmationPageArguments( Application $membershipApplication, string $updateToken ): array {
		return [
			'membershipApplication' => $this->getApplicationArguments( $membershipApplication, $updateToken ),
			'address' => $this->getAddressArguments( $membershipApplication->getApplicant() ),
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
			'paymentType' => $membershipApplication->getPayment()->getPaymentMethod()->getId(),
			'status' => $this->mapStatus( $membershipApplication->isConfirmed() ),
			'membershipFee' => $membershipApplication->getPayment()->getAmount()->getEuroString(),
			'paymentIntervalInMonths' => $membershipApplication->getPayment()->getIntervalInMonths(),
			'updateToken' => $updateToken
		];
	}

	private function getAddressArguments( Applicant $applicant ): array {
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
			// Generating bank name and BIC from IBAN because not all data is stored in $payment.bankData
			$bankData = $this->bankDataGenerator->getBankDataFromIban( $payment->getBankData()->getIban() );
			return [
				'iban' => $bankData->getIban()->toString(),
				'bic' => $bankData->getBic(),
				'bankName' => $bankData->getBankName(),
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

	public function presentApplicationWasAnonymized(): void {
		$this->exception = new AccessDeniedException( 'access_denied_membership_confirmation_anonymized' );
	}

	public function presentAccessViolation(): void {
		$this->exception = new AccessDeniedException( 'access_denied_membership_confirmation' );
	}

	public function presentTechnicalError( string $message ): void {
		$this->exception = new \RuntimeException( $message );
	}

}
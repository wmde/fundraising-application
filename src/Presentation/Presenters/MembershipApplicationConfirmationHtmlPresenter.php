<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use DateTime;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\MembershipContext\Domain\Model\Applicant;
use WMDE\Fundraising\MembershipContext\Domain\Model\ApplicantName;
use WMDE\Fundraising\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationPresenter;
use WMDE\Fundraising\PaymentContext\Domain\BankDataGenerator;
use WMDE\Fundraising\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentMethod;
use WMDE\Fundraising\PaymentContext\Domain\Model\PayPalPayment;

/**
 * @license GPL-2.0-or-later
 */
class MembershipApplicationConfirmationHtmlPresenter implements ShowApplicationConfirmationPresenter {

	private $template;
	private $bankDataGenerator;
	private $html = '';
	private $urlGenerator;

	/**
	 * @var \Exception|null
	 */
	private $exception = null;

	public function __construct( TwigTemplate $template, BankDataGenerator $bankDataGenerator, UrlGenerator $urlGenerator ) {
		$this->template = $template;
		$this->bankDataGenerator = $bankDataGenerator;
		$this->urlGenerator = $urlGenerator;
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
			),
			'urls' => [
				'cancelMembership'  => $this->urlGenerator->generateRelativeUrl(
					'cancel-membership-application',
					[
						'id' => $membershipApplication->getId(),
						'updateToken' => $updateToken,
					]
				)
			]
		];
	}

	private function getApplicationArguments( Application $membershipApplication, string $updateToken ): array {
		$incentives = iterator_to_array( $membershipApplication->getIncentives() );
		return [
			'id' => $membershipApplication->getId(),
			'membershipType' => $membershipApplication->getType(),
			'paymentType' => $membershipApplication->getPayment()->getPaymentMethod()->getId(),
			'status' => $this->mapStatus( $membershipApplication->isConfirmed() ),
			'membershipFee' => $membershipApplication->getPayment()->getAmount()->getEuroString(),
			'paymentIntervalInMonths' => $membershipApplication->getPayment()->getIntervalInMonths(),
			'updateToken' => $updateToken,
			'incentive' => count( $incentives ) > 0 ? $incentives[0]->getName() : ''
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
			'email' => $applicant->getEmailAddress()->getFullAddress(),
			'countryCode' => $applicant->getPhysicalAddress()->getCountryCode(),
			'applicantType' => $applicant->isPrivatePerson() ? ApplicantName::PERSON_PRIVATE : ApplicantName::PERSON_COMPANY
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

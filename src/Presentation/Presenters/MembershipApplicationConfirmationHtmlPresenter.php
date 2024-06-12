<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\MembershipContext\Domain\Model\Applicant;
use WMDE\Fundraising\MembershipContext\Domain\Model\ApplicantName;
use WMDE\Fundraising\MembershipContext\Domain\Model\MembershipApplication;
use WMDE\Fundraising\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationPresenter;

class MembershipApplicationConfirmationHtmlPresenter implements ShowApplicationConfirmationPresenter {

	private string $html = '';

	private ?\Exception $exception = null;

	public function __construct( private readonly TwigTemplate $template ) {
	}

	/**
	 * @param MembershipApplication $application
	 * @param array<string, scalar> $paymentData
	 */
	public function presentConfirmation( MembershipApplication $application, array $paymentData ): void {
		$this->html = $this->template->render(
			$this->getConfirmationPageArguments(
				$application,
				$paymentData,
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

	/**
	 * @param MembershipApplication $membershipApplication
	 * @param array<string, scalar> $paymentData
	 *
	 * @return array<string, mixed>
	 */
	private function getConfirmationPageArguments( MembershipApplication $membershipApplication, array $paymentData ): array {
		return [
			'membershipApplication' => $this->getApplicationArguments( $membershipApplication, $paymentData ),
			'address' => $this->getAddressArguments( $membershipApplication->getApplicant() ),
			'bankData' => [
				'iban' => $paymentData['iban'] ?? '',
				'bic' => $paymentData['bic'] ?? '',
				'bankname' => $paymentData['bankname'] ?? '',
			]
		];
	}

	/**
	 * @param MembershipApplication $membershipApplication
	 * @param array<string, scalar> $paymentData
	 *
	 * @return array<string, mixed>
	 */
	private function getApplicationArguments( MembershipApplication $membershipApplication, array $paymentData ): array {
		/** @var int $amount */
		$amount = $paymentData['amount'];
		return [
			'id' => $membershipApplication->getId(),
			'membershipType' => $membershipApplication->getType(),
			'paymentType' => $paymentData['paymentType'],
			'status' => 'status-booked',
			// TODO: Adapt the front end to take cents here for currency localisation
			'membershipFee' => Euro::newFromCents( $amount )->getEuroFloat(),
			'membershipFeeInCents' => $amount,
			'paymentIntervalInMonths' => $paymentData['interval'],
			// TODO - this is deprecated, the template should not use the updateToken on its own and instead should use the provided URLs
			'updateToken' => '',
			'incentives' => iterator_to_array( $membershipApplication->getIncentives() )
		];
	}

	/**
	 * @param Applicant $applicant
	 *
	 * @return array<string, string>
	 */
	private function getAddressArguments( Applicant $applicant ): array {
		return [
			'salutation' => $applicant->getName()->salutation,
			'title' => $applicant->getName()->title,
			'fullName' => $applicant->getName()->getFullName(),
			'streetAddress' => $applicant->getPhysicalAddress()->streetAddress,
			'postalCode' => $applicant->getPhysicalAddress()->postalCode,
			'city' => $applicant->getPhysicalAddress()->city,
			'email' => $applicant->getEmailAddress()->getFullAddress(),
			'countryCode' => $applicant->getPhysicalAddress()->countryCode,
			'applicantType' => $applicant->isPrivatePerson() ? ApplicantName::PERSON_PRIVATE : ApplicantName::PERSON_COMPANY
		];
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

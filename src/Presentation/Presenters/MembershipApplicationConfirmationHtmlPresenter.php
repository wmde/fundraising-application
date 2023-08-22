<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Authentication\MembershipUrlAuthenticationLoader;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\MembershipContext\Domain\Model\Applicant;
use WMDE\Fundraising\MembershipContext\Domain\Model\ApplicantName;
use WMDE\Fundraising\MembershipContext\Domain\Model\MembershipApplication;
use WMDE\Fundraising\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationPresenter;

/**
 * @license GPL-2.0-or-later
 */
class MembershipApplicationConfirmationHtmlPresenter implements ShowApplicationConfirmationPresenter {

	private string $html = '';

	private ?\Exception $exception = null;

	public function __construct(
		private readonly TwigTemplate $template,
		private readonly UrlGenerator $urlGenerator,
		private readonly MembershipUrlAuthenticationLoader $urlAuthenticationLoader
	) {
	}

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

	private function getConfirmationPageArguments( MembershipApplication $membershipApplication, array $paymentData ): array {
		$cancelMembershipUrl = $this->urlGenerator->generateAbsoluteUrl(
			Routes::CANCEL_MEMBERSHIP,
			[ 'id' => $membershipApplication->getId() ]
		);
		$authenticator = $this->urlAuthenticationLoader->getMembershipUrlAuthenticator( $membershipApplication->getId() );

		return [
			'membershipApplication' => $this->getApplicationArguments( $membershipApplication, $paymentData ),
			'address' => $this->getAddressArguments( $membershipApplication->getApplicant() ),
			'bankData' => [
				'iban' => $paymentData['iban'] ?? '',
				'bic' => $paymentData['bic'] ?? '',
				'bankname' => $paymentData['bankname'] ?? '',
			],
			'urls' => [
				// This is deprecated, we can't cancel memberships in the frontend any more
				'cancelMembership'  => $authenticator->addAuthenticationTokensToApplicationUrl( $cancelMembershipUrl )
			]
		];
	}

	private function getApplicationArguments( MembershipApplication $membershipApplication, array $paymentData ): array {
		return [
			'id' => $membershipApplication->getId(),
			'membershipType' => $membershipApplication->getType(),
			'paymentType' => $paymentData['paymentType'],
			'status' => 'status-booked',
			// TODO: Adapt the front end to take cents here for currency localisation
			'membershipFee' => Euro::newFromCents( $paymentData['amount'] )->getEuroFloat(),
			'membershipFeeInCents' => $paymentData['amount'],
			'paymentIntervalInMonths' => $paymentData['interval'],
			// TODO - this is deprecated, the template should not use the updateToken on its own and instead should use the provided URLs
			'updateToken' => '',
			'incentives' => iterator_to_array( $membershipApplication->getIncentives() )
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

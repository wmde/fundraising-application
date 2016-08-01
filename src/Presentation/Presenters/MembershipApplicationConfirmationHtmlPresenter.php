<?php

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\MembershipApplicant;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\MembershipApplication;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\ShowMembershipApplicationConfirmation\ShowMembershipAppConfirmationResponse;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * Render the confirmation pages for membership applications
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MembershipApplicationConfirmationHtmlPresenter {

	private $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function present( ShowMembershipAppConfirmationResponse $response ): string {
		return $this->template->render(
			$this->getConfirmationPageArguments(
				$response->getApplication(),
				$response->getUpdateToken()
			)
		);
	}

	private function getConfirmationPageArguments( MembershipApplication $membershipApplication, string $updateToken ): array {
		return [
			'membershipApplication' => $this->getApplicationArguments( $membershipApplication, $updateToken ),
			'person' => $this->getPersonArguments( $membershipApplication->getApplicant() ),
			'bankData' => $this->getBankDataArguments( $membershipApplication->getPayment()->getBankData() )
		];
	}

	private function getApplicationArguments( MembershipApplication $membershipApplication, string $updateToken ): array {
		return [
			'id' => $membershipApplication->getId(),
			'membershipFee' => $membershipApplication->getPayment()->getAmount()->getEuroString(),
			'paymentIntervalInMonths' => $membershipApplication->getPayment()->getIntervalInMonths(),
			'updateToken' => $updateToken
		];
	}

	private function getPersonArguments( MembershipApplicant $applicant ): array {
		return [
			'salutation' => $applicant->getPersonName()->getSalutation(),
			'title' => $applicant->getPersonName()->getTitle(),
			'fullName' => $applicant->getPersonName()->getFullName(),
			'streetAddress' => $applicant->getPhysicalAddress()->getStreetAddress(),
			'postalCode' => $applicant->getPhysicalAddress()->getPostalCode(),
			'city' => $applicant->getPhysicalAddress()->getCity(),
			'email' => $applicant->getEmailAddress(),
		];
	}

	private function getBankDataArguments( BankData $bankData ): array {
		return [
			'iban' => $bankData->getIban()->toString(),
			'bic' => $bankData->getBic(),
			'bankName' => $bankData->getBankName(),
		];
	}

}
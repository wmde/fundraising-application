<?php

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplicant;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplication;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipResponse;

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

	public function present( ApplyForMembershipResponse $response ): string {
		return $this->template->render(
			$this->getConfirmationPageArguments(
				$response->getMembershipApplication(),
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
			'intervalText' => $membershipApplication->getPayment()->getIntervalInMonths(),
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
<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\UseCases\FeeChange\ShowFeeChangePresenter;

class MembershipFeeUpgradeHTMLPresenter implements ShowFeeChangePresenter {
	private string $responseString = '';

	public function __construct( private readonly FunFunFactory $ffFactory) {
	}

	public function showFeeChangeForm(
		string $uuid,
		int $externalMemberId,
		int $currentAmountInCents,
		int $suggestedAmountInCents,
		int $currentInterval
	): void {
		$this->responseString = $this->ffFactory->getLayoutTemplate( 'Membership_Fee_Upgrade.html.twig' )->render(
			[
				'uuid' => $uuid,
				'externalMemberId' => $externalMemberId,
				'currentAmountInCents' => $currentAmountInCents,
				'suggestedAmountInCents' => $suggestedAmountInCents,
				'currentInterval' => $currentInterval,
				'feeChangeFrontendFlag' => MembershipFeeUpgradeFrontendFlag::SHOW_FEE_CHANGE_FORM
			]
		);
	}

	public function showFeeChangeError(): void {
		// displaying an error message will be handled by the frontend code
		$this->responseString = $this->ffFactory->getLayoutTemplate( 'Membership_Fee_Upgrade.html.twig' )->render(
			[
				'uuid' => '',
				'currentAmountInCents' => '',
				'suggestedAmountInCents' => '',
				'currentInterval' => '',
				'feeChangeFrontendFlag' => MembershipFeeUpgradeFrontendFlag::SHOW_ERROR_PAGE
			]
		);
	}

	public function showFeeChangeAlreadyFilled(): void {
		// displaying the info message will be handled by the frontend code
		$this->responseString = $this->ffFactory->getLayoutTemplate( 'Membership_Fee_Upgrade.html.twig' )->render(
			[
				'uuid' => '',
				'currentAmountInCents' => '',
				'suggestedAmountInCents' => '',
				'currentInterval' => '',
				'feeChangeFrontendFlag' => MembershipFeeUpgradeFrontendFlag::SHOW_FEE_ALREADY_CHANGED_PAGE
			]
		);
	}

	public function getHTMLResponse(): Response {
		return new Response( $this->responseString );
	}
}
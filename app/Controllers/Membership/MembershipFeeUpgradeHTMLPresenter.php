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
		int $externalMemberId,
		int $currentAmountInCents,
		int $suggestedAmountInCents,
		int $currentInterval
	): void {
		$this->responseString = $this->ffFactory->getLayoutTemplate( 'Membership_Fee_Upgrade.html.twig' )->render(
			[
				'uuid' => 0, //TODO where do we get the correct UUID from
				'externalMemberId' => $externalMemberId,
				'currentAmountInCents' => $currentAmountInCents,
				'suggestedAmountInCents' => $suggestedAmountInCents,
				'currentInterval' => $currentInterval,
				'showErrorPageInstead' => false
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
				'showErrorPageInstead' => true
			]
		);
	}

	public function showFeeChangeAlreadyFilled(): void {
		// TODO: only show a simple "system message" html page  / static html page with a styled message
		$this->responseString = '';
	}

	public function getHTMLResponse(): Response {
		return new Response( $this->responseString );
	}
}
<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\MembershipContext\UseCases\FeeChange\ShowFeeChangePresenter;

class MembershipFeeChangeHTMLPresenter implements ShowFeeChangePresenter {
	private string $responseString = '';

	public function __construct( private readonly TwigTemplate $twigTemplate, private readonly UrlGenerator $urlGenerator ) {
	}

	public function showFeeChangeForm(
		string $uuid,
		int $externalMemberId,
		int $currentAmountInCents,
		int $suggestedAmountInCents,
		int $currentInterval
	): void {
		$this->responseString = $this->twigTemplate->render(
			[
				'uuid' => $uuid,
				'externalMemberId' => $externalMemberId,
				'currentAmountInCents' => $currentAmountInCents,
				'suggestedAmountInCents' => $suggestedAmountInCents,
				'currentInterval' => $currentInterval,
				'feeChangeFrontendFlag' => MembershipFeeUpgradeFrontendFlag::SHOW_FEE_CHANGE_FORM,
				'urls' => Routes::getNamedRouteUrls( $this->urlGenerator ),
			]
		);
	}

	public function showFeeChangeError(): void {
		// displaying an error message will be handled by the frontend code
		$this->responseString = $this->twigTemplate->render(
			[
				'uuid' => '',
				'currentAmountInCents' => 0,
				'suggestedAmountInCents' => 0,
				'currentInterval' => 0,
				'feeChangeFrontendFlag' => MembershipFeeUpgradeFrontendFlag::SHOW_ERROR_PAGE,
				'urls' => Routes::getNamedRouteUrls( $this->urlGenerator ),
			]
		);
	}

	public function showFeeChangeAlreadyFilled(): void {
		// displaying the info message will be handled by the frontend code
		$this->responseString = $this->twigTemplate->render(
			[
				'uuid' => '',
				'currentAmountInCents' => 0,
				'suggestedAmountInCents' => 0,
				'currentInterval' => 0,
				'feeChangeFrontendFlag' => MembershipFeeUpgradeFrontendFlag::SHOW_FEE_ALREADY_CHANGED_PAGE,
				'urls' => Routes::getNamedRouteUrls( $this->urlGenerator ),
			]
		);
	}

	public function getHTMLResponse(): Response {
		return new Response( $this->responseString );
	}
}

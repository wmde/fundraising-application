<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\MembershipContext\UseCases\CancelMembershipApplication\CancellationResponse;

/**
 * @license GPL-2.0-or-later
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CancelMembershipApplicationHtmlPresenter {

	private TwigTemplate $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function present( CancellationResponse $response ): string {
		return $this->template->render( [
			'membershipId' => $response->getMembershipApplicationId(),
			'cancellationSuccessful' => $response->isSuccess()
		] );
	}

}

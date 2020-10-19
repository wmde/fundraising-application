<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\UseCases\CancelMembershipApplication\CancellationRequest;

class CancelMembershipApplicationController {

	public function index( FunFunFactory $ffFactory, Request $request ): string {
		$cancellationRequest = new CancellationRequest(
			(int)$request->query->get( 'id', '' )
		);

		return $ffFactory->newCancelMembershipApplicationHtmlPresenter()->present(
			$ffFactory->newCancelMembershipApplicationUseCase( $request->query->get( 'updateToken', '' ) )
				->cancelApplication( $cancellationRequest )
		);
	}
}

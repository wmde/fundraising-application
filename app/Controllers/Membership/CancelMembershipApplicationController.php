<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\UseCases\CancelMembershipApplication\CancellationRequest;

class CancelMembershipApplicationController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$cancellationRequest = new CancellationRequest(
			(int)$request->query->get( 'id', '' )
		);

		$result = $ffFactory->newCancelMembershipApplicationUseCase( $request->query->get( 'updateToken', '' ) )
			->cancelApplication( $cancellationRequest );

		return new Response( $ffFactory->newCancelMembershipApplicationHtmlPresenter()->present( $result ) );
	}
}

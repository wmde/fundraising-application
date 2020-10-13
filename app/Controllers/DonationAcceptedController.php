<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class DonationAcceptedController {

	public function handle( FunFunFactory $ffFactory, Request $request ): JsonResponse {
		$eventHandler = $ffFactory->newDonationAcceptedEventHandler(
			$request->query->get( 'update_token', '' )
		);
		$result = $eventHandler->onDonationAccepted( (int)$request->query->get( 'donation_id', '' ) );

		return JsonResponse::create(
			$result === null ? [ 'status' => 'OK' ] : [ 'status' => 'ERR', 'message' => $result ]
		);
	}
}

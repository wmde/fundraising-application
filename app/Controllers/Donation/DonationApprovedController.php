<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Donation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * This class receives an HTTP request from the Fundraising Operation Center for sending a notification email.
 *
 * This controller exists because we have not yet shared the mail templating code (esp. for the confirmation mail)
 * between Fundraising Frontend and Fundraising Operation Center.
 *
 * We're tracking progress and ideas for improving the situation in https://phabricator.wikimedia.org/T254028
 */
class DonationApprovedController {

	public function index( FunFunFactory $ffFactory, Request $request ): JsonResponse {
		$eventHandler = $ffFactory->newDonationApprovedEventHandler(
			$request->query->get( 'update_token', '' )
		);
		$result = $eventHandler->onDonationApproved( (int)$request->query->get( 'donation_id', '' ) );

		return new JsonResponse(
			$result === null ? [ 'status' => 'OK' ] : [ 'status' => 'ERR', 'message' => $result ]
		);
	}
}

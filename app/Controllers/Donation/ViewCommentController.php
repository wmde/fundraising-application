<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Donation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class ViewCommentController {

	public function handle( FunFunFactory $ffFactory, Request $request ): Response {
		$template = $ffFactory->getLayoutTemplate(
			'Donation_Comment.html.twig'
		);

		return new Response(
			$template->render(
				[
					'donationId' => (int)$request->query->get( 'donationId', '' ),
					'updateToken' => $request->query->get( 'updateToken', '' ),
					'cancelUrl' => $ffFactory->getUrlGenerator()->generateRelativeUrl(
						'show-donation-confirmation',
						[
							'id' => (int)$request->query->get( 'donationId', '' ),
							'accessToken' => $request->query->get( 'accessToken', '' )
						]
					)
				]
			)
		);
	}
}

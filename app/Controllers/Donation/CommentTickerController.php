<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Donation;

use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class CommentTickerController {
	public function index( FunFunFactory $ffFactory ): Response {
		$template = $ffFactory->getLayoutTemplate(
			'Comment_Ticker.html.twig'
		);

		return new Response( $template->render( [] ) );
	}
}

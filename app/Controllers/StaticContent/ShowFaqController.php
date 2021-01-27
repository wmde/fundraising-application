<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\StaticContent;

use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class ShowFaqController {

	public function index( FunFunFactory $ffFactory ): Response {
		$template = $ffFactory->getLayoutTemplate( 'Frequent_Questions.html.twig' );
		return new Response( $template->render(
			[
				'faq_content' => $ffFactory->getFaqContent(),
			]
		) );
	}
}

<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\StaticContent;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class ShowFaqController {

	public function handle( FunFunFactory $ffFactory ): string {
		return $ffFactory->getLayoutTemplate( 'Frequent_Questions.html.twig' )->render(
			[
				'faq_content' => $ffFactory->getFaqContent(),
			]
		);
	}
}

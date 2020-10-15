<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\StaticContent;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class ShowContactFormController {

	public function handle( FunFunFactory $ffFactory ): string {
		return $ffFactory->getLayoutTemplate( 'Contact_Form.html.twig' )->render(
			[
				'contact_categories' => $ffFactory->getGetInTouchCategories(),
				'contactFormValidationPatterns' => $ffFactory->getValidationRules()->contactForm,
			]
		);
	}
}

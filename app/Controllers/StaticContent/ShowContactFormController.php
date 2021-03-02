<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\StaticContent;

use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class ShowContactFormController {

	public function index( FunFunFactory $ffFactory ): Response {
		$template = $ffFactory->getLayoutTemplate( 'Contact_Form.html.twig' );
		$templateContext = [
			'contact_categories' => $ffFactory->getGetInTouchCategories(),
			'contactFormValidationPatterns' => $ffFactory->getValidationRules()->contactForm,
		];
		return new Response( $template->render( $templateContext ) );
	}
}

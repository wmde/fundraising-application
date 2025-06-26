<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\StaticContent;

use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class ShowPatternLibraryController {
	public function index( FunFunFactory $ffFactory, string $pattern ): Response {
		$template = $ffFactory->getLayoutTemplate( 'Pattern_Library.html.twig' );
		return new Response( $template->render( [ 'pattern' => $pattern, ] ) );
	}
}

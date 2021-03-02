<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\StaticContent;

use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class ShowUseOfFundsController {

	public function index( FunFunFactory $ffFactory ): Response {
		$renderUseOfFunds = $ffFactory->getUseOfFundsRenderer();
		return new Response( $renderUseOfFunds() );
	}
}

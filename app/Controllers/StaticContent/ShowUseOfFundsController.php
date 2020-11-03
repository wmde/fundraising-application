<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\StaticContent;

use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class ShowUseOfFundsController {

	public function index( FunFunFactory $ffFactory, Request $request ): string {
		$renderer = $ffFactory->getUseOfFundsRenderer();
		return $renderer( $request );
	}
}

<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\StaticContent;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\PaymentContext\DataAccess\Sofort\Transfer\Request;

class ShowUseOfFundsController {

	public function index( FunFunFactory $ffFactory, Request $request ): string {
		$renderer = $ffFactory->getUseOfFundsRenderer();
		return $renderer( $request );
	}
}

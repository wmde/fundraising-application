<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Cache\AuthorizedCachePurger;

class PurgeCacheController {

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$response = $ffFactory->newAuthorizedCachePurger()->purgeCache( $request->query->get( 'secret', '' ) );

		return new Response(
			[
				AuthorizedCachePurger::RESULT_SUCCESS => 'SUCCESS',
				AuthorizedCachePurger::RESULT_ERROR => 'ERROR',
				AuthorizedCachePurger::RESULT_ACCESS_DENIED => 'ACCESS DENIED'
			][$response]
		);
	}
}

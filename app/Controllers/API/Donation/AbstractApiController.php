<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\API\Donation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractApiController {

	protected function errorResponse( string $message, int $responseCode, array $errors = [] ): Response {
		return new JsonResponse( [ 'ERR' => $message, 'errors' => $errors ], $responseCode );
	}
}

<?php

namespace WMDE\Fundraising\Frontend\App;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @license GPL-2.0-or-later
 */
class AccessDeniedException extends HttpException {

	public function __construct( ?string $message = null, int $code = 0, ?\Throwable $previousException = null ) {
		parent::__construct( Response::HTTP_FORBIDDEN, $message, $previousException, [], $code );
	}
}

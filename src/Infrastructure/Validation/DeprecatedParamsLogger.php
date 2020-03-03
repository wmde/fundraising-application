<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Validation;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @license GNU GPL v2+
 */
class DeprecatedParamsLogger {


	public static function logParamUsage( LoggerInterface $logger, Request $request ) {
		foreach ( [ 'betrag', 'periode', 'zahlweise' ] as $deprecatedParameter ) {
			if( $request->request->has( $deprecatedParameter ) ){
				$logger->notice(
					"Some application is still submitting the deprecated form parameter '{$deprecatedParameter}'"
				);
			}
		}
	}
}
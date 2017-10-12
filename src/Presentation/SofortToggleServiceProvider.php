<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class SofortToggleServiceProvider implements ServiceProviderInterface, BootableProviderInterface {

	private const QUERY_PARAM_NAME = 'pmt';

	private $paymentTypesSettings;

	public function __construct( PaymentTypesSettings $paymentTypesSettings ) {
		$this->paymentTypesSettings = $paymentTypesSettings;
	}

	public function register( Container $app ): void {
	}

	public function boot( Application $app ): void {
		$app->before( function( Request $request ): void {
			if ( $request->query->get( self::QUERY_PARAM_NAME ) === '0' ) {
				$this->paymentTypesSettings->updateSetting( 'SUB', PaymentTypesSettings::PURPOSE_DONATION, false );
			}
		}, Application::EARLY_EVENT );
	}
}

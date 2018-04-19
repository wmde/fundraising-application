<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Toggle Sofort payment availablity by using a URL parameter
 *
 * This is for A/B testing different payment types.
 * See discussion on https://phabricator.wikimedia.org/T162380 for possible refactorings to this class.
 */
class SofortToggleServiceProvider implements ServiceProviderInterface, BootableProviderInterface {

	private const QUERY_PARAM_NAME = 'pmt';

	private $paymentTypesSettings;

	public function __construct( PaymentTypesSettings $paymentTypesSettings ) {
		$this->paymentTypesSettings = $paymentTypesSettings;
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function register( Container $app ): void {
		// empty function for satisfying the interface
	}

	public function boot( Application $app ): void {
		$app->before( function( Request $request ): void {
			if ( $request->query->get( self::QUERY_PARAM_NAME ) === '0' ) {
				$this->paymentTypesSettings->setSettingToFalse( 'SUB', PaymentTypesSettings::ENABLE_DONATIONS );
			}
		}, Application::EARLY_EVENT );
	}
}

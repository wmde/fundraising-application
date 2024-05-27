<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Airbrake\MonologHandler as AirbrakeHandler;
use Airbrake\Notifier;
use Monolog\Handler\HandlerInterface;
use Monolog\Level;
use Psr\Log\LogLevel;

class ErrbitLoggerFactory {

	/**
	 * Data from PayPal IPN we want to have in ErrBit for faster debugging.
	 *
	 * This is just for convenience/reference, you can find the complete data in the paypal log
	 *
	 */
	private const ALLOWED_PPL_PARAMS = [
		  "invoice",
		  "payment_date",
		  "payment_status",
		  "charset",
		  "notify_version",
		  "txn_id",
		  "payment_type",
		  "txn_type",
		  "item_name",
		  "ipn_track_id",
	];

	/**
	 * Regular expression that matches URL paths for PayPal IPN end points, {@see config/routing.yaml}
	 */
	private const PAYPAL_URL_MATCH = '!/handle-paypal-payment-notification!';

	/**
	 * @param string $projectId
	 * @param string $projectKey
	 * @param string $host
	 * @param string $environment
	 * @param 'ALERT'|'Alert'|'alert'|'CRITICAL'|'Critical'|'critical'|'DEBUG'|'Debug'|'debug'|'EMERGENCY'|'Emergency'|'emergency'|'ERROR'|'Error'|'error'|'INFO'|'Info'|'info'|'NOTICE'|'Notice'|'notice'|'WARNING'|'Warning'|'warning' $level
	 * @param bool $bubble
	 */
	public static function createErrbitHandler( string $projectId, string $projectKey, string $host, string $environment = 'dev', ?string $level = LogLevel::DEBUG, bool $bubble = true ): HandlerInterface {
		$notifier = new Notifier( [
			'projectId' => $projectId,
			'projectKey' => $projectKey,
			'host' => $host,
			'environment' => $environment,
			'remoteConfig' => false
		] );

		$notifier->addFilter( static function ( $notice ) {
			$currentUrl = $notice['context']['url'] ?? '';
			if ( !preg_match( self::PAYPAL_URL_MATCH, $currentUrl ) ) {
				return $notice;
			}
			$newParams = [];
			foreach ( self::ALLOWED_PPL_PARAMS as $paramName ) {
				if ( !empty( $notice['params'][$paramName] ) ) {
					$newParams[$paramName] = $notice['params'][$paramName];
				}
			}
			$notice['params'] = $newParams;
			return $notice;
		} );

		return new AirbrakeHandler( $notifier, Level::fromName( $level ?? LogLevel::DEBUG )->value, $bubble );
	}

}

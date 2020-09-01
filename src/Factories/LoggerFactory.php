<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Airbrake\MonologHandler as AirbrakeHandler;
use Airbrake\Notifier;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;
use WMDE\Fundraising\Frontend\Infrastructure\SupportHandler;

/**
 * @license GPL-2.0-or-later
 */
class LoggerFactory {

	private const TYPE_ERROR_LOG = 'error_log';
	private const TYPE_FILE = 'file';
	private const TYPE_ERRBIT = 'errbit';

	private array $config;

	public function __construct( array $config ) {
		$this->config = $config;
	}

	public function getLogger(): Logger {
		$handlers = [];
		foreach ( $this->config['handlers'] as $handlerParams ) {
			$handlers[] = $this->newHandler( $handlerParams );
		}

		return new Logger( 'application', $handlers );
	}

	private function newHandler( array $config ): HandlerInterface {
		switch ( $config['method'] ?? '' ) {
			case self::TYPE_ERROR_LOG:
				return new ErrorLogHandler( ErrorLogHandler::OPERATING_SYSTEM, $config['level'] );
			case self::TYPE_FILE:
				return new StreamHandler( $config['url'], $config['level'] );

			case self::TYPE_ERRBIT:
				if ( empty( $config['projectId'] ) || empty( $config['projectKey'] ) || empty( $config['host'] ) ) {
					throw new \InvalidArgumentException( 'You need to configure project ID, projectKey and host for errbit logging' );
				}
				$notifier = new Notifier( [
					'projectId' => $config['projectId'],
					'projectKey' => $config['projectKey'],
					'host' => $config['host'],
					'environment' => $_ENV['APP_ENV'] ?? 'dev'
				] );

				return new SupportHandler(
					new AirbrakeHandler( $notifier, $config['level'] ),
					new Logger( 'errbit errors', [ new ErrorLogHandler( ErrorLogHandler::OPERATING_SYSTEM, LogLevel::ERROR ), ] ),
				);

			default:
				throw new \InvalidArgumentException( 'Unknown logging method - ' . $config['method'] );
		}
	}

}

<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Airbrake\MonologHandler as AirbrakeHandler;
use Airbrake\Notifier;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Log\LogLevel;
use WMDE\Fundraising\Frontend\Infrastructure\SupportHandler;

class ErrbitLoggerFactory {
	public static function createErrbitHandler( string $projectId, string $projectKey, string $host, string $environment = 'dev', ?string $level = LogLevel::DEBUG, bool $bubble = true ): HandlerInterface {
		$notifier = new Notifier( [
			'projectId' => $projectId,
			'projectKey' => $projectKey,
			'host' => $host,
			'environment' => $environment
		] );

		// Wrap errbit handler in SupportHandler to avoid logception (logger trying to log logging errors) when errbit throws an error
		return new SupportHandler(
			new AirbrakeHandler( $notifier, Logger::toMonologLevel( $level ?? LogLevel::DEBUG ), $bubble ),
			new Logger( 'errbit errors', [ new ErrorLogHandler( ErrorLogHandler::OPERATING_SYSTEM, LogLevel::ERROR ), ] ),
		);
	}

}

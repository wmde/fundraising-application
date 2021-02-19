<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Airbrake\MonologHandler as AirbrakeHandler;
use Airbrake\Notifier;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Log\LogLevel;

class ErrbitLoggerFactory {
	public static function createErrbitHandler( string $projectId, string $projectKey, string $host, string $environment = 'dev', ?string $level = LogLevel::DEBUG, bool $bubble = true ): HandlerInterface {
		$notifier = new Notifier( [
			'projectId' => $projectId,
			'projectKey' => $projectKey,
			'host' => $host,
			'environment' => $environment
		] );

		return new AirbrakeHandler( $notifier, Logger::toMonologLevel( $level ?? LogLevel::DEBUG ), $bubble );
	}

}

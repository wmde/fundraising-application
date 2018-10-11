<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Gelf\Publisher;
use Gelf\Transport\UdpTransport;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * @license GNU GPL v2+
 */
class LoggerFactory {

	private const TYPE_ERROR_LOG = 'error_log';
	private const TYPE_FILE = 'file';
	private const TYPE_GELF = 'gelf';

	private $config;

	public function __construct( array $config ) {
		$this->config = $config;
	}

	public function getLogger(): Logger {
		$logger = new Logger( 'application', [ $this->newHandler() ] );
		return $logger;
	}

	private function newHandler(): HandlerInterface {
		switch ( $this->config['method'] ?? '' ) {
			case self::TYPE_ERROR_LOG:
				return new ErrorLogHandler( ErrorLogHandler::OPERATING_SYSTEM, $this->config['level'] );
			case self::TYPE_FILE:
				return new StreamHandler( $this->config['url'], $this->config['level'] );
			case self::TYPE_GELF:
				$url = parse_url( $this->config['url'] );
				return new GelfHandler(
					new Publisher(
						new UdpTransport( $url['host'], $url['port'] ?? UdpTransport::DEFAULT_PORT )
					),
					$this->config['level']
				);
			default:
				throw new \InvalidArgumentException( 'Unknown logging method - ' . $this->config['method'] );
		}
	}

}
<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use Monolog\Handler\AbstractHandler;
use Psr\Log\LoggerInterface;

class SupportHandler extends AbstractHandler {

	private $wrappedErrorProneHandler;
	private $loggerForHandlerErrors;

	public function __construct( AbstractHandler $wrappedErrorProneHandler, LoggerInterface $loggerForHandlerErrors ) {
		parent::__construct();
		$this->wrappedErrorProneHandler = $wrappedErrorProneHandler;
		$this->loggerForHandlerErrors = $loggerForHandlerErrors;

	}

	public function handle( array $record ): bool {
		try {
			return $this->wrappedErrorProneHandler->handle( $record );
		} catch ( \Throwable $e ) {
			$this->loggerForHandlerErrors->error( $e->getMessage(), ['exception' => $e] );
		}
		return false;
	}

	public function getLevel(): int {
		return $this->wrappedErrorProneHandler->getLevel();
	}
}
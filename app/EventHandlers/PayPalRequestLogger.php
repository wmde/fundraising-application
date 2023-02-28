<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PayPalRequestLogger implements EventSubscriberInterface {

	private string $logFilePath;
	private array $notificationRoutes;
	private LoggerInterface $errorLog;

	public function __construct( string $logFilePath, array $notificationRoutes, LoggerInterface $errorLog ) {
		$this->logFilePath = $logFilePath;
		$this->notificationRoutes = $notificationRoutes;
		$this->errorLog = $errorLog;
	}

	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::REQUEST => 'logPayPalRequest'
		];
	}

	public function logPayPalRequest( RequestEvent $event ): void {
		$request = $event->getRequest();

		if ( !in_array( $request->attributes->get( '_route' ), $this->notificationRoutes ) ) {
			return;
		}

		$filesystem = new Filesystem();
		try {
			$filesystem->appendToFile( $this->logFilePath, $this->getLogLine( $request ) );
		} catch ( IOException $e ) {
			$this->errorLog->error( $e->getMessage() );
		}
	}

	private function getLogLine( Request $request ): string {
		return implode( ',', [
			$request->get( 'txn_type', '' ),
			$request->get( 'txn_id', '' ),
			$request->get( 'subscr_id', '' ),
			$request->get( 'item_number', 0 )
		] ) . PHP_EOL;
	}
}

<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PayPalRequestLogger implements EventSubscriberInterface {

	private string $logFilePath;
	private array $notificationRoutes;

	public function __construct( string $logFilePath, array $notificationRoutes ) {
		$this->logFilePath = $logFilePath;
		$this->notificationRoutes = $notificationRoutes;
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

		try {
			$filesystem = new Filesystem();
			$filesystem->appendToFile( $this->logFilePath, $this->getLogLine( $request ) );
		}
		catch ( \Exception $e ) {
			echo $e->getMessage();
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

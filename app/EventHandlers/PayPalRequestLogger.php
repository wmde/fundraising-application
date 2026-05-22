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
use WMDE\Fundraising\Frontend\App\RequestSearcher;

class PayPalRequestLogger implements EventSubscriberInterface {

	public function __construct(
		private readonly string $logFilePath,
		private readonly array $notificationRoutes,
		private readonly LoggerInterface $errorLog
	) {
	}

	/**
	 * @return array<string, string>
	 */
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
			RequestSearcher::get( $request, 'txn_type', '' ),
			RequestSearcher::get( $request, 'txn_id', '' ),
			RequestSearcher::get( $request, 'subscr_id', '' ),
			RequestSearcher::get( $request, 'item_number', 0 )
		] ) . PHP_EOL;
	}
}

<?php
declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * This event modifies redirect responses and adds the bucket parameters to the URL.
 * This allows us to do bucket tests without storing anything on the client side.
 */
class AppendBucketParametersForRedirect implements EventSubscriberInterface {

	public function __construct( private readonly FunFunFactory $factory ) {
	}

	/**
	 * @return array<string, array{string, int}>
	 */
	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::RESPONSE => 'appendBucketParameters',
		];
	}

	public function appendBucketParameters( ResponseEvent $event ): void {
		$response = $event->getResponse();
		if ( !$response->isRedirect() ) {
			return;
		}

		$location = $response->headers->get( 'Location', '' );

		if ( $this->locationIsApplicationUrl( $location, $event->getRequest() ) ) {
			return;
		}

		$response->headers->set(
			'Location',
			$location . $this->getUrlParameters( $location )
		);
	}

	private function locationIsApplicationUrl( ?string $location, Request $request ): bool {
		return !str_starts_with( $location, $request->getSchemeAndHttpHost() ) ||
			// The standard says 'Location' header should be absolute, but we can't be sure
			str_starts_with( $location, '/' );
	}

	private function getUrlParameters( string $location ): string {
		$params = [];
		foreach ( $this->factory->getSelectedBuckets() as $bucket ) {
			$params = array_merge( $params, $bucket->getParameters() );
		}

		$separator = !str_contains( $location, '?' ) ? '?' : '&';
		return $separator . http_build_query( $params );
	}
}

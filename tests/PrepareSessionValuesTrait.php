<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This trait allows functional tests to prepare a specified session state.
 *
 * From Symfony 5.3 onwards, you're no longer supposed to access the session object in the container directly,
 * because the session is part of the Request object. This means you have to inject the session into the request object.
 * This trait uses an event handler that overrides the lazy session initialization set by
 * \Symfony\Component\HttpKernel\EventListener\SessionListener with an initialized session.
 *
 * Future Symfony versions may introduce different ways to set values in the session before making a request,
 * see https://github.com/symfony/symfony/issues/44253
 *
 */
trait PrepareSessionValuesTrait {
	public function prepareSessionValues( array $values ): void {
		// This priority should be higher than the EventSubscriber priorities that access the session,
		// e.g. TrackBannerDonationRedirects
		$eventSetterPriority = 8;
		static::getContainer()->get( 'event_dispatcher' )->addListener(
			KernelEvents::REQUEST,
			function ( RequestEvent $event ) use ( $values ) {
				/** @var Session $session */
				$session = static::getContainer()->get( 'session.factory' )->createSession();
				foreach ( $values as $k => $v ) {
					$session->set( $k, $v );
				}
				$event->getRequest()->setSession( $session );
			},
			$eventSetterPriority
		);
	}
}

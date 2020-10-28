<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\DomainEventHandler\BucketLoggingHandler;

/**
 * Set consent state of BucketLoggingHandler on every request, depending on the consent cookie value.
 *
 * This will prevent the BucketLoggingHandler from logging the buckets when no consent was given.
 */
class BucketLoggingConsentHandler implements EventSubscriberInterface {

	private BucketLoggingHandler $bucketLoggingHandler;

	public static function getSubscribedEvents() {
		return [
			KernelEvents::REQUEST => 'onKernelRequest'
		];
	}

	public function __construct( BucketLoggingHandler $bucketLoggingHandler ) {
		$this->bucketLoggingHandler = $bucketLoggingHandler;
	}

	public function onKernelRequest( GetResponseEvent $event ): void {
		$this->bucketLoggingHandler->setConsentGiven(
			$event->getRequest()->cookies->get( 'cookie_consent' ) === 'yes'
		);
	}
}

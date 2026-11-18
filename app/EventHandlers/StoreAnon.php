<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class StoreAnon implements EventSubscriberInterface {

	private const int PRIORITY = 512;

	public function __construct(
		private readonly FunFunFactory $factory
	) {
	}

	/**
	 * @return array<string, array{string, int}>
	 */
	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::REQUEST => [ 'setAnon', self::PRIORITY ]
		];
	}

	public function setAnon( RequestEvent $event ): void {
		if ( $event->getRequest()->query->getInt( 'anon' ) === 1 ) {
			$this->factory->setAnon();
		}
	}
}

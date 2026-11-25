<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class StoreFeatures implements EventSubscriberInterface {

	private const int PRIORITY = 512;

	public function __construct(
		private readonly FunFunFactory $factory,
		private readonly array $allowedFeatures
	) {
	}

	/**
	 * @return array<string, array{string, int}>
	 */
	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::REQUEST => [ 'setFeatures', self::PRIORITY ]
		];
	}

	public function setFeatures( RequestEvent $event ): void {
		$features = [];

		foreach ( $this->allowedFeatures as $feature ) {
			if ( $event->getRequest()->query->getInt( $feature ) === 1 ) {
				$features[] = $feature;
			}
		}

		$this->factory->setFeatures( $features );
	}
}

<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class StoreTheme implements EventSubscriberInterface {

	private const PRIORITY = 256;

	public function __construct(
		private readonly FunFunFactory $factory,
		private readonly array $allowedThemes
	) {
	}

	/**
	 * @return array<string, array{string, int}>
	 */
	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::REQUEST => [ 'setTheme', self::PRIORITY ],
		];
	}

	public function setTheme( RequestEvent $event ): void {
		$request = $event->getRequest();
		$theme = $request->query->get( 'theme' );

		if ( !in_array( $theme, $this->allowedThemes ) ) {
			$theme = $this->allowedThemes[0];
		}

		$this->factory->setTheme( $theme );
	}
}

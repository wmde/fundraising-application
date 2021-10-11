<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use WMDE\Fundraising\Frontend\App\CookieNames;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class StoreLocale implements EventSubscriberInterface {

	private const PRIORITY = 512;

	private FunFunFactory $factory;
	private array $allowedLocales;

	public function __construct( FunFunFactory $factory, array $allowedLocales ) {
		$this->factory = $factory;
		$this->allowedLocales = $allowedLocales;
	}

	public static function getSubscribedEvents() {
		return [
			KernelEvents::REQUEST => [ 'setLocale', self::PRIORITY ],
		];
	}

	public function setLocale( RequestEvent $requestEvent ) {
		$request = $requestEvent->getRequest();
		$locale = $this->findLocale( $request );

		if ( !in_array( $locale, $this->allowedLocales ) ) {
			$locale = $this->allowedLocales[0];
		}

		$request->setLocale( $locale );
		$this->factory->setLocale( $locale );
	}

	private function findLocale( Request $request ): string {
		if ( $request->cookies->has( CookieNames::LOCALE ) ) {
			return $request->cookies->get( CookieNames::LOCALE );
		}

		if ( $request->query->has( CookieNames::LOCALE ) ) {
			return $request->query->get( CookieNames::LOCALE );
		}

		return $request->getPreferredLanguage() ?? '';
	}
}

<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use WMDE\Fundraising\Frontend\App\CookieNames;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class StoreLocale implements EventSubscriberInterface {

	public const SHOULD_STORE_LOCALE_COOKIE = 'shouldStoreLocaleCookie';
	private const PRIORITY = 512;

	private FunFunFactory $factory;
	private array $allowedLocales;

	public function __construct( FunFunFactory $factory, array $allowedLocales ) {
		$this->factory = $factory;
		$this->allowedLocales = $allowedLocales;
	}

	/**
	 * @return array<string, array{string, int}>
	 */
	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::REQUEST => [ 'setLocale', self::PRIORITY ],
			KernelEvents::RESPONSE => [ 'storeLocaleCookie', self::PRIORITY ],
		];
	}

	public function setLocale( RequestEvent $requestEvent ): void {
		$request = $requestEvent->getRequest();
		$locale = $this->findLocale( $request );

		if ( !in_array( $locale, $this->allowedLocales ) ) {
			$locale = $this->allowedLocales[0];
		}

		$request->setLocale( $locale );
		$this->factory->setLocale( $locale );
	}

	public function storeLocaleCookie( ResponseEvent $event ): void {
		$locale = $event->getRequest()->attributes->get( self::SHOULD_STORE_LOCALE_COOKIE );
		if ( !$locale || !in_array( $locale, $this->allowedLocales ) ) {
			return;
		}

		// TODO: Review the best way to create this cookie
		$event->getResponse()->headers->setCookie(
			new Cookie(
				CookieNames::LOCALE,
				$locale,
				0,
				'/',
				null,
				false,
				false,
				false,
				"Lax"
			)
		);
	}

	private function findLocale( Request $request ): string {
		if ( $request->cookies->has( CookieNames::LOCALE ) ) {
			return $request->cookies->get( CookieNames::LOCALE );
		}

		if ( $request->query->has( CookieNames::LOCALE ) ) {
			$locale = $request->query->get( CookieNames::LOCALE );
			$request->attributes->set( self::SHOULD_STORE_LOCALE_COOKIE, $locale );
			return $locale;
		}

		return $request->getPreferredLanguage() ?? '';
	}
}

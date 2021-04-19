<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use WMDE\Fundraising\Frontend\App\CookieNames;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class StoreBucketSelection implements EventSubscriberInterface {

	public const SHOULD_STORE_BUCKET_COOKIE = 'shouldStoreBucketCookie';
	private const PRIORITY = 256;

	private FunFunFactory $factory;

	public function __construct( FunFunFactory $factory ) {
		$this->factory = $factory;
	}

	public static function getSubscribedEvents() {
		return [
			KernelEvents::REQUEST => [ 'setSelectedBuckets', self::PRIORITY ],
			KernelEvents::RESPONSE => [ 'storeSelectedBuckets', self::PRIORITY ]
		];
	}

	public function setSelectedBuckets( RequestEvent $event ): void {
		$request = $event->getRequest();
		parse_str( $request->cookies->get( CookieNames::BUCKET_TESTING, '' ), $cookieValues );
		$selector = $this->factory->getBucketSelector();
		$this->factory->setSelectedBuckets( $selector->selectBuckets( $cookieValues, $request->query->all() ) );

		$request->attributes->set(
			self::SHOULD_STORE_BUCKET_COOKIE,
			$request->cookies->get( CookieNames::CONSENT ) === 'yes'
		);
	}

	public function storeSelectedBuckets( ResponseEvent $event ): void {
		if ( !$event->getRequest()->attributes->get( self::SHOULD_STORE_BUCKET_COOKIE ) ) {
			return;
		}

		$event->getResponse()->headers->setCookie(
			$this->factory->getCookieBuilder()->newCookie(
				CookieNames::BUCKET_TESTING,
				$this->getCookieValue(),
				$this->getCookieLifetime()
			)
		);
	}

	private function getCookieValue(): string {
		return http_build_query(
			// each Bucket returns one [ key => value ], they all need to be merged into one array
			array_merge( ...$this->getParameterArrayFromSelectedBuckets() )
		);
	}

	private function getParameterArrayFromSelectedBuckets(): array {
		return array_map(
			function ( Bucket $bucket ) {
				return $bucket->getParameters();
			},
			$this->factory->getSelectedBuckets()
		);
	}

	private function getCookieLifetime(): ?int {
		$mostDistantCampaign = $this->factory->getCampaignCollection()->getMostDistantCampaign();
		if ( $mostDistantCampaign === null ) {
			return null;
		}
		return $mostDistantCampaign->getEndTimestamp()->getTimestamp();
	}

}

<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class StoreBucketSelection implements EventSubscriberInterface {

	private const PRIORITY = 256;
	private const COOKIE_NAME = 'spenden_ttg';

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

	public function setSelectedBuckets( KernelEvent $event ): void {
		$request = $event->getRequest();
		parse_str( $request->cookies->get( self::COOKIE_NAME, '' ), $cookieValues );
		$selector = $this->factory->getBucketSelector();
		$this->factory->setSelectedBuckets( $selector->selectBuckets( $cookieValues, $request->query->all() ) );
	}

	public function storeSelectedBuckets( FilterResponseEvent $event ): void {
		$response = $event->getResponse();
		$response->headers->setCookie(
			$this->factory->getCookieBuilder()->newCookie(
				self::COOKIE_NAME,
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

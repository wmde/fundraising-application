<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class StoreBucketSelection implements EventSubscriberInterface {

	private const PRIORITY = 256;

	private FunFunFactory $factory;

	public function __construct( FunFunFactory $factory ) {
		$this->factory = $factory;
	}

	public static function getSubscribedEvents() {
		return [
			KernelEvents::REQUEST => [ 'setSelectedBuckets', self::PRIORITY ],
		];
	}

	public function setSelectedBuckets( RequestEvent $event ): void {
		$request = $event->getRequest();
		$selector = $this->factory->getBucketSelector();
		$this->factory->setSelectedBuckets( $selector->selectBuckets( $request->query->all() ) );
	}
}

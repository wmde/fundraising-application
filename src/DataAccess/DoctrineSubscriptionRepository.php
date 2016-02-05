<?php


namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\DBAL\Connection;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Domain\SubscriptionRepository;
use WMDE\Fundraising\Store\Factory as FundraisingStoreFactory;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class DoctrineSubscriptionRepository implements SubscriptionRepository {

	private $entityManager;

	public function __construct( Connection $connection ) {
		$factory = new FundraisingStoreFactory( $connection );
		$this->entityManager = $factory->getEntityManager();
	}

	public function storeSubscription( Subscription $subscription ) {
		$this->entityManager->persist( $subscription );
	}

}
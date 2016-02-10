<?php


namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Domain\SubscriptionRepository;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class DbalSubscriptionRepository implements SubscriptionRepository {

	private $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	public function storeSubscription( Subscription $subscription ) {
		$this->entityManager->persist( $subscription );
		$this->entityManager->persist( $subscription->getAddress() );
		$this->entityManager->flush();
	}

	public function countSimilar( Subscription $subscription, \DateTime $cutoffDateTime ): int {
		$qb = $this->entityManager->createQueryBuilder();
		$query = $qb->select( 'COUNT( s.id )' )
				->from( Subscription::class, 's' )
				->where( $qb->expr()->eq( 's.email', ':email' ) )
				->andWhere( $qb->expr()->gt( 's.createdAt', ':cutoffDate' ) )
				->setParameter( 'email', $subscription->getEmail() )
				->setParameter( 'cutoffDate', $cutoffDateTime, \Doctrine\DBAL\Types\Type::DATETIME )
				->getQuery();
		return (int) $query->getSingleScalarResult();
	}

	public function findByConfirmationCode( string $confirmationCode ) {
		return $this->entityManager->getRepository( Subscription::class )->findOneBy( [
			'confirmationCode' => hex2bin( $confirmationCode )
		] );
	}

}
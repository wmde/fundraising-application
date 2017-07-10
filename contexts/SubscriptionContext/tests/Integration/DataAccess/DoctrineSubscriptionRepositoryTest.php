<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\SubscriptionContext\DataAccess;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\Address;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\SubscriptionContext\DataAccess\DoctrineSubscriptionRepository;
use WMDE\Fundraising\Frontend\SubscriptionContext\Domain\Repositories\SubscriptionRepository;
use WMDE\Fundraising\Frontend\SubscriptionContext\Domain\Repositories\SubscriptionRepositoryException;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\SubscriptionContext\DataAccess\DoctrineSubscriptionRepository
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class DoctrineSubscriptionRepositoryTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp(): void {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
		parent::setUp();
	}

	private function getOrmRepository(): EntityRepository {
		return $this->entityManager->getRepository( Subscription::class );
	}

	public function testGivenASubscription_itIsStored(): void {
		$subscription = new Subscription();
		$subscription->setEmail( 'nyan@awesomecats.com' );
		$subscription->setAddress( new Address() );
		$repository = new DoctrineSubscriptionRepository( $this->entityManager );
		$repository->storeSubscription( $subscription );
		$expected = $this->getOrmRepository()->findAll();
		$this->assertEquals( [$subscription], $expected );
	}

	public function testGivenARecentSubscription_itIsCounted(): void {
		$firstSubscription = $this->persistFirstSubscription();
		$this->entityManager->flush();
		$repository = new DoctrineSubscriptionRepository( $this->entityManager );
		$this->assertSame( 1, $repository->countSimilar( $firstSubscription, new \DateTime( '100 years ago' ) ) );
	}

	public function testMultipleSubscriptions_onlySimilarAreCounted(): void {
		$this->persistFirstSubscription();
		$this->persistSecondSubscription();
		$thirdSubscription = $this->persistThirdSubscription();

		$this->entityManager->flush();
		$repository = new DoctrineSubscriptionRepository( $this->entityManager );
		$this->assertSame( 1, $repository->countSimilar( $thirdSubscription, new \DateTime( '1 hour ago' ) ) );
		$this->assertSame( 2, $repository->countSimilar( $thirdSubscription, new \DateTime( '100 years ago' ) ) );
	}

	public function testDatabaseLayerExceptionsAreConvertedToDomainExceptions(): void {
		$entityManager = $this->getMockBuilder( EntityManager::class )
			->setMethods( [ 'getRepository', 'getClassMetadata', 'persist', 'flush' ] )
			->disableOriginalConstructor()
			->getMock();

		$entityManager->expects( $this->once() )->method( 'persist' )->willThrowException( new ORMException() );
		$repository = new DoctrineSubscriptionRepository( $entityManager );
		$subscription = new Subscription();
		$subscription->setEmail( 'nyan@awesomecats.com' );
		$subscription->setAddress( new Address() );

		$this->expectException( SubscriptionRepositoryException::class );
		$repository->storeSubscription( $subscription );
	}

	private function persistFirstSubscription(): Subscription {
		$subscription = new Subscription();
		$subscription->setEmail( 'nyan@awesomecats.com' );
		$subscription->setCreatedAt( new \DateTime( '10 minutes ago' ) );
		$this->entityManager->persist( $subscription );
		return $subscription;
	}

	private function persistSecondSubscription(): Subscription {
		$subscription = new Subscription();
		$subscription->setEmail( 'unicorn@dancingonrainbows.com' );
		$subscription->setCreatedAt( new \DateTime( '10 days ago' ) );
		$this->entityManager->persist( $subscription );
		return $subscription;
	}

	private function persistThirdSubscription(): Subscription {
		$subscription = new Subscription();
		$subscription->setEmail( 'unicorn@dancingonrainbows.com' );
		$subscription->setCreatedAt( new \DateTime( '10 minutes ago' ) );
		$this->entityManager->persist( $subscription );
		return $subscription;
	}
}

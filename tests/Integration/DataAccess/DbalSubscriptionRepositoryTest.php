<?php


namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\Address;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\DataAccess\DbalSubscriptionRepository;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\DataAccess\DbalSubscriptionRepository
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class DbalSubscriptionRepositoryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	/**
	 * @var Connection
	 */
	private $connection;

	public function setUp() {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
		parent::setUp();
	}

	private function getOrmRepository() {
		return $this->entityManager->getRepository( Subscription::class );
	}

	public function testGivenASubscription_itIsStored() {
		$subscription = new Subscription();
		$subscription->setEmail( 'nyan@awesomecats.com' );
		$subscription->setAddress( new Address() );
		$repository = new DbalSubscriptionRepository( $this->entityManager );
		$repository->storeSubscription( $subscription );
		$expected = $this->getOrmRepository()->findAll();
		$this->assertEquals( [$subscription], $expected );
	}

	public function testGivenARecentSubscription_itIsCounted() {
		$firstSubscription = $this->persistFirstSubscription();
		$this->entityManager->flush();
		$repository = new DbalSubscriptionRepository(  $this->entityManager );
		$this->assertSame( 1, $repository->countSimilar( $firstSubscription, new \DateTime( '100 years ago' ) ) );
	}

	public function testMultipleSubscriptions_onlySimilarAreCounted() {
		$this->persistFirstSubscription();
		$this->persistSecondSubscription();
		$thirdSubscription = $this->persistThirdSubscription();

		$this->entityManager->flush();
		$repository = new DbalSubscriptionRepository(  $this->entityManager );
		$this->assertSame( 1, $repository->countSimilar( $thirdSubscription, new \DateTime( '1 hour ago' ) ) );
		$this->assertSame( 2, $repository->countSimilar( $thirdSubscription, new \DateTime( '100 years ago' ) ) );
	}

	private function persistFirstSubscription(): Subscription {
		$subscription = new Subscription();
		$subscription->setEmail( 'nyan@awesomecats.com' );
		$subscription->setCreatedAt( new \DateTime( '10 minutes ago' ) );
		$this->entityManager->persist( $subscription );
		return $subscription;
	}

	private function persistSecondSubscription() {
		$subscription = new Subscription();
		$subscription->setEmail( 'unicorn@dancingonrainbows.com' );
		$subscription->setCreatedAt( new \DateTime( '10 days ago' ) );
		$this->entityManager->persist( $subscription );
		return $subscription;
	}

	private function persistThirdSubscription() {
		$subscription = new Subscription();
		$subscription->setEmail( 'unicorn@dancingonrainbows.com' );
		$subscription->setCreatedAt( new \DateTime( '10 minutes ago' ) );
		$this->entityManager->persist( $subscription );
		return $subscription;
	}
}

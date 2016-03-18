<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit;

use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Domain\Repositories\SubscriptionRepository;
use WMDE\Fundraising\Frontend\Validation\SubscriptionDuplicateValidator;

class SubscriptionDuplicateValidatorTest extends \PHPUnit_Framework_TestCase {

	public function testGivenSubscriptionCountOfZero_validationSucceeds() {
		$repository = $this->getMockBuilder( SubscriptionRepository::class )->disableOriginalConstructor()->getMock();
		$repository->method( 'countSimilar' )->willReturn( 0 );
		$subscription = $this->getMock( Subscription::class );
		$cutoffDateTime = new \DateTime( '3 hours ago' );
		$validator = new SubscriptionDuplicateValidator( $repository, $cutoffDateTime );
		$this->assertTrue( $validator->validate( $subscription )->isSuccessful() );
	}

	public function testGivenSubscriptionCountGreaterThanZero_validationFails() {
		$repository = $this->getMockBuilder( SubscriptionRepository::class )->disableOriginalConstructor()->getMock();
		$repository->method( 'countSimilar' )->willReturn( 1 );
		$subscription = $this->getMock( Subscription::class );
		$cutoffDateTime = new \DateTime( '3 hours ago' );
		$validator = new SubscriptionDuplicateValidator( $repository, $cutoffDateTime );
		$this->assertFalse( $validator->validate( $subscription )->isSuccessful() );
	}

}

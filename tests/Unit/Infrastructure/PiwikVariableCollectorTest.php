<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Infrastructure\PiwikEvents;
use WMDE\Fundraising\Frontend\Infrastructure\PiwikVariableCollector;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\PiwikVariableCollector
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PiwikVariableCollectorTest extends \PHPUnit\Framework\TestCase {

	const INITIAL_AMOUNT = '34.56';
	const INITIAL_TYPE = 'BEZ';
	const INITIAL_INTERVAL = 0;

	const ACTUAL_AMOUNT = '12.34';
	const ACTUAL_TYPE = 'UEB';
	const ACTUAL_INTERVAL = 3;

	/** @dataProvider sessionAndDonationDataProvider */
	public function testVariableCollectorReturnsCorrectEvents( ?string $initialAmount, ?string $initialType,
																?string $initialInterval, array $expectedResult ): void {
		$sessionData = $this->newSessionData( $initialAmount, $initialType, $initialInterval );
		$donation = $this->newDonationMock( self::ACTUAL_AMOUNT, self::ACTUAL_TYPE, self::ACTUAL_INTERVAL );

		$tracking = PiwikVariableCollector::newForDonation( $sessionData, $donation );
		$this->assertCustomTrackingContainsEvents( $expectedResult, $tracking->getEvents() );
	}

	public function sessionAndDonationDataProvider(): array {
		return [
			[
				self::INITIAL_AMOUNT,
				self::INITIAL_TYPE,
				self::INITIAL_INTERVAL,
				[
					[ 'setCustomVariable', 1, 'Payment', 'BEZ/UEB', PiwikEvents::SCOPE_VISIT ],
					[ 'setCustomVariable', 2, 'Amount', '34.56/12.34', PiwikEvents::SCOPE_VISIT ],
					[ 'setCustomVariable', 3, 'Interval', '0/3', PiwikEvents::SCOPE_VISIT ]
				],
			],
			[
				self::INITIAL_AMOUNT,
				self::INITIAL_TYPE,
				null,
				[
					[ 'setCustomVariable', 1, 'Payment', 'BEZ/UEB', PiwikEvents::SCOPE_VISIT ],
					[ 'setCustomVariable', 2, 'Amount', '34.56/12.34', PiwikEvents::SCOPE_VISIT ]
				],
			],
			[
				null,
				self::INITIAL_TYPE,
				self::INITIAL_INTERVAL,
				[
					[ 'setCustomVariable', 1, 'Payment', 'BEZ/UEB', PiwikEvents::SCOPE_VISIT ],
					[ 'setCustomVariable', 3, 'Interval', '0/3', PiwikEvents::SCOPE_VISIT ]
				],
			],
			[
				null,
				null,
				null,
				[],
			]
		];
	}

	private function assertCustomTrackingContainsEvents( array $expectedEvents, array $actualEvents ): void {
		foreach ( $expectedEvents as $event ) {
			$this->assertContains( $event, $actualEvents );
		}

		$this->assertCount( count( $expectedEvents ), $actualEvents );
	}

	private function newDonationMock( string $amount, string $paymentType, int $paymentInterval ): Donation {
		$amount = Euro::newFromString( $amount );

		$donationMock = $this->getMockBuilder( Donation::class )->disableOriginalConstructor()->getMock();
		$donationMock->method( 'getPaymentMethodId' )->willReturn( $paymentType );
		$donationMock->method( 'getPaymentIntervalInMonths' )->willReturn( $paymentInterval );
		$donationMock->method( 'getAmount' )->willReturn( $amount );
		return $donationMock;
	}

	private function newSessionData( ?string $amount, ?string $paymentType, ?string $paymentInterval ): array {
		return [
			'paymentAmount' => $amount,
			'paymentType' => $paymentType,
			'paymentInterval' => $paymentInterval
		];
	}

}

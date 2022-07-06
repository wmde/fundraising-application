<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Routes;

use DateTime;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\App\Controllers\Payment\SofortNotificationController;
use WMDE\Fundraising\PaymentContext\RequestModel\SofortNotificationRequest;

/**
 * @covers \WMDE\Fundraising\PaymentContext\RequestModel\SofortNotificationRequest
 */
class SofortNotificationRequestTest extends TestCase {

	public function testValidRequest_requestIsConstructed(): void {
		$this->markTestIncomplete( "This will need to be updated when updating the donation controllers" );

		$content = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n"
			. '<status_notification><transaction>555-777</transaction>'
			. '<time>2010-04-14T19:01:08+02:00</time>'
			. '</status_notification>';
		$request = SofortNotificationController::fromUseCaseRequestFromRequestContent( $content );

		$this->assertInstanceOf( SofortNotificationRequest::class, $request );
		$this->assertSame( '555-777', $request->getTransactionId() );
		$this->assertEquals( new DateTime( '2010-04-14T19:01:08+02:00' ), $request->getTime() );
		$this->assertNull( $request->getDonationId(), 'Donation id is not part of the vendor raw request.' );
	}

	public function testAbsurdRequest_nullIsReturned(): void {
		$this->markTestIncomplete( "This will need to be updated when updating the donation controllers" );

		$request = SofortNotificationController::fromUseCaseRequestFromRequestContent( 'fff' );

		$this->assertNull( $request );
	}

	public function testMissingTransactionIdRequest_nullIsReturned(): void {
		$this->markTestIncomplete( "This will need to be updated when updating the donation controllers" );

		$content = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n"
			. '<status_notification>'
			. '<time>2013-06-25T11:04:03+05:00</time>'
			. '</status_notification>';
		$request = SofortNotificationController::fromUseCaseRequestFromRequestContent( $content );

		$this->assertNull( $request );
	}

	public function testInValidTimeRequest_nullIsReturned(): void {
		$this->markTestIncomplete( "This will need to be updated when updating the donation controllers" );

		$content = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n"
			. '<status_notification><transaction>555-777</transaction>'
			. '<time>now</time>'
			. '</status_notification>';
		$request = SofortNotificationController::fromUseCaseRequestFromRequestContent( $content );

		$this->assertNull( $request );
	}
}

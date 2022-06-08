<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Mail;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\BasicMailSubjectRenderer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeTranslator;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentMethod;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Mail\BasicMailSubjectRenderer
 */
class BasicMailSubjectRendererTest extends TestCase {

	public function testGivenDonation_givenSubjectLineIsReturned() {
		$this->markTestIncomplete( "This will need to be updated when updating the donation controllers" );
		$templateArguments['donation']['paymentType'] = PaymentMethod::PAYPAL;
		$this->assertSame(
			'mail_subject_getintouch',
			$this->newDonationConfirmationMailSubjectRenderer()->render( $templateArguments )
		);
	}

	public function newDonationConfirmationMailSubjectRenderer(): BasicMailSubjectRenderer {
		return new BasicMailSubjectRenderer(
			new FakeTranslator(),
			'mail_subject_getintouch'
		);
	}
}

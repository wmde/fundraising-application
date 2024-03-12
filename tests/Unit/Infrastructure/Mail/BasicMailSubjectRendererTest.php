<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Mail;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\BasicMailSubjectRenderer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeTranslator;
use WMDE\Fundraising\PaymentContext\Domain\PaymentType;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Mail\BasicMailSubjectRenderer
 */
class BasicMailSubjectRendererTest extends TestCase {

	public function testGivenDonation_givenSubjectLineIsReturned(): void {
		$templateArguments['donation']['paymentType'] = PaymentType::Paypal->value;
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

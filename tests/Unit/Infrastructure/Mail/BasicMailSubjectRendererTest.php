<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Mail;

use Symfony\Component\Translation\Translator;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\BasicMailSubjectRenderer;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentMethod;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Mail\BasicMailSubjectRenderer
 */
class BasicMailSubjectRendererTest extends \PHPUnit\Framework\TestCase {

	public function testGivenDonation_givenSubjectLineIsReturned() {
		$templateArguments['donation']['paymentType'] = PaymentMethod::PAYPAL;
		$this->assertSame(
			'mail_subject_getintouch',
			$this->newDonationConfirmationMailSubjectRenderer()->render( $templateArguments )
		);
	}

	public function newDonationConfirmationMailSubjectRenderer(): BasicMailSubjectRenderer {
		return new BasicMailSubjectRenderer(
			new Translator( 'zz_ZZ' ),
			'mail_subject_getintouch'
		);
	}
}

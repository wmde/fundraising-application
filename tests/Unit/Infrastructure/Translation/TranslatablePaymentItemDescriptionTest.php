<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Translation;

use PHPUnit\Framework\TestCase;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatablePaymentItemDescription;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatorInterface;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentInterval;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatablePaymentItemDescription
 */
class TranslatablePaymentItemDescriptionTest extends TestCase {

	public function testOnGetText_returnsTranslatedText(): void {
		$translator = $this->createMock( TranslatorInterface::class );
		$translator->expects( $this->once() )
			->method( 'trans' )
			->with( 'amount: %amount% | interval: %interval%', [ '%amount%' => 42.42, '%interval%' => 0 ] )
			->willReturn( 'amount: 42.42 | interval: 0' );

		$translatable = new TranslatablePaymentItemDescription(
			'amount: %amount% | interval: %interval%',
			$translator
		);

		$translatedText = $translatable->getText( Euro::newFromCents( 4242 ), PaymentInterval::OneTime );

		$this->assertSame( 'amount: 42.42 | interval: 0', $translatedText );
	}
}

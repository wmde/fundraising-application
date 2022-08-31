<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Translation;

use PHPUnit\Framework\TestCase;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatablePaymentItemDescription;
use WMDE\Fundraising\Frontend\Tests\Fixtures\InMemoryTranslator;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentInterval;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatablePaymentItemDescription
 */
class TranslatablePaymentItemDescriptionTest extends TestCase {

	public function testOnGetText_returnsTranslatedText(): void {
		$translator = new InMemoryTranslator( [
			'credit_card_item_name_donation' => 'you are donating %amount% %interval%',
			'payment_interval_0' => 'one-time'
		] );

		$translatable = new TranslatablePaymentItemDescription( 'credit_card_item_name_donation', $translator );

		$translatedText = $translatable->getText( Euro::newFromCents( 4242 ), PaymentInterval::OneTime );

		$this->assertSame( 'you are donating 42.42 one-time', $translatedText );
	}
}

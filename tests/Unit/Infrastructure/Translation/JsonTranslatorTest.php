<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Translation;

use FileFetcher\InMemoryFileFetcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\JsonTranslator;

#[CoversClass( JsonTranslator::class )]
class JsonTranslatorTest extends TestCase {

	public function testGivenATranslationKey_translatorLooksUpTranslation(): void {
		$translator = new JsonTranslator( $this->newTranslationSource() );
		$translator->addFile( 'testytest_messages.json' );

		$translatedText = $translator->trans( 'donate_now' );

		$this->assertSame( 'Jetzt spenden', $translatedText );
	}

	public function testGivenATranslationKeyWithParams_translatorReplacesPlaceholders(): void {
		$translator = new JsonTranslator( $this->newTranslationSource() );
		$translator->addFile( 'testytest_messages.json' );

		$translatedText = $translator->trans( 'you_will_pay', [
			'%amount%' => '5 %currency%',
			'%interval%' => 'monatlich',
			'%currency%' => 'EUR'
		] );

		$this->assertSame( 'Sie spenden 5 EUR monatlich', $translatedText );
	}

	public function testGivenAnUnkownTranslationKey_translatorThrowsAnException(): void {
		$translator = new JsonTranslator( $this->newTranslationSource() );
		$translator->addFile( 'testytest_messages.json' );

		$this->expectException( \InvalidArgumentException::class );

		$translatedText = $translator->trans( 'whacky_message' );

		$this->assertSame( 'Jetzt spenden', $translatedText );
	}

	private function newTranslationSource(): InMemoryFileFetcher {
		$source = new InMemoryFileFetcher(
			[
				'testytest_messages.json' => json_encode(
					[
						'donate_now' => 'Jetzt spenden',
						'you_will_pay' => 'Sie spenden %amount% %interval%'
					]
				) ]
		);
		return $source;
	}

}

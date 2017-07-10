<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System;

use WMDE\Fundraising\Frontend\Validation\TextPolicyValidator;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\TextPolicyValidator
 *
 * @licence GNU GPL v2+
 * @author Christoph Fischer <christoph.fischer@wikimedia.de
 */
class TextPolicyValidatorTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider urlTestProvider
	 */
	public function testWhenGivenCommentHasURL_validatorReturnsFalse( $commentToTest ): void {
		$this->skipIfNoInternet();

		$textPolicyValidator = new TextPolicyValidator();

		$this->assertFalse( $textPolicyValidator->hasHarmlessContent(
			$commentToTest,
			TextPolicyValidator::CHECK_URLS | TextPolicyValidator::CHECK_URLS_DNS
		) );
	}

	private function skipIfNoInternet(): void {
		static $isConnected = null;

		if ( $isConnected === null ) {
			$isConnected = (bool)@fsockopen( 'www.google.com', 80, $num, $error, 1 );
		}

		if ( !$isConnected ) {
			$this->markTestSkipped( 'No internet connection' );
		}
	}

	public function urlTestProvider() {
		return [
			[ 'www.example.com' ],
			[ 'http://www.example.com' ],
			[ 'https://www.example.com' ],
			[ 'example.com' ],
			[ 'example.com/test' ],
			[ 'example.com/teKAst/index.php' ],
			[ 'Ich mag Wikipedia. Aber meine Seite ist auch toll:example.com/teKAst/index.php' ],
			[ 'inwx.berlin' ],
			[ 'wwwwwww.website.com' ],
			[ 'TriebWerk-Grün.de' ],
		];
	}

	/**
	 * @dataProvider harmlessTestProvider
	 */
	public function testWhenGivenHarmlessComment_validatorReturnsTrue( $commentToTest ): void {
		$this->skipIfNoInternet();

		$textPolicyValidator = new TextPolicyValidator();

		$this->assertTrue( $textPolicyValidator->hasHarmlessContent(
			$commentToTest,
			TextPolicyValidator::CHECK_URLS | TextPolicyValidator::CHECK_URLS_DNS | TextPolicyValidator::CHECK_BADWORDS
		) );
	}

	public function harmlessTestProvider() {
		return [
			[ 'Wikipedia ist so super, meine Eltern sagen es ist eine toll Seite. Berlin ist auch Super.' ],
			[ 'Ich mag Wikipedia. Aber meine Seite ist auch toll. Googelt mal nach Bunsenbrenner!!!1' ],
			[ 'Bei Wikipedia kann man eine Menge zum Thema Hamster finden. Hamster fressen voll viel Zeug alter!' ],
			[ 'Manche Seiten haben keinen Inhalt, das finde ich sch...e' ], // this also tests the domain detection
		];
	}

	public function testHamlessContentWithDns(): void {
		$this->skipIfNoInternet();

		if ( checkdnsrr( 'some-non-existing-domain-drfeszrfdaesr.sdferdyerdhgty', 'A' ) ) {
			// https://www.youtube.com/watch?v=HGBOeLdm-1s
			$this->markTestSkipped( 'Your DNS/ISP provider gives results for impossible host names.' );
		}

		$textPolicyValidator = new TextPolicyValidator();

		$this->assertTrue( $textPolicyValidator->hasHarmlessContent(
			'Ich mag Wikipedia.Wieso ? Weil ich es so toll finde!',
			TextPolicyValidator::CHECK_URLS | TextPolicyValidator::CHECK_URLS_DNS | TextPolicyValidator::CHECK_BADWORDS
		) );
	}

	/**
	 * @dataProvider insultingTestProvider
	 */
	public function testWhenGivenInsultingComment_validatorReturnsFalse( $commentToTest ): void {
		$textPolicyValidator = $this->getPreFilledTextPolicyValidator();

		$this->assertFalse( $textPolicyValidator->hasHarmlessContent(
			$commentToTest,
			TextPolicyValidator::CHECK_BADWORDS
		) );
	}

	public function insultingTestProvider() {
		return [
			[ 'Alles Deppen!' ],
			[ 'Heil Hitler!' ],
			[ 'Duhamsterfresse!!!' ],
			[ 'Alles nur HAMSTERFRESSEN!!!!!!!!1111111111' ],
		];
	}

	/**
	 * @dataProvider whiteWordsInsultingTestProvider
	 */
	public function testWhenGivenInsultingCommentAndWhiteWords_validatorReturnsFalse( $commentToTest ): void {
		$textPolicyValidator = $this->getPreFilledTextPolicyValidator();

		$this->assertFalse(
			$textPolicyValidator->hasHarmlessContent(
				$commentToTest,
				TextPolicyValidator::CHECK_URLS
				| TextPolicyValidator::CHECK_URLS_DNS
				| TextPolicyValidator::CHECK_BADWORDS
				| TextPolicyValidator::IGNORE_WHITEWORDS
			)
		);
	}

	public function whiteWordsInsultingTestProvider() {
		return [
			[ 'Ich heisse Deppendorf ihr Deppen und das ist auch gut so!' ],
			[ 'Ihr Arschgeigen, ich wohne in Marsch und das ist auch gut so!' ],
			[ 'Bei Wikipedia gibts echt tolle Arschkrampen!' ],
		];
	}

	/**
	 * @dataProvider whiteWordsHarmlessTestProvider
	 */
	public function testWhenGivenHarmlessCommentAndWhiteWords_validatorReturnsTrue( $commentToTest ): void {
		$textPolicyValidator = $this->getPreFilledTextPolicyValidator();

		$this->assertTrue(
			$textPolicyValidator->hasHarmlessContent(
				$commentToTest,
				TextPolicyValidator::CHECK_URLS
				| TextPolicyValidator::CHECK_URLS_DNS
				| TextPolicyValidator::CHECK_BADWORDS
				| TextPolicyValidator::IGNORE_WHITEWORDS
			)
		);
	}

	public function whiteWordsHarmlessTestProvider() {
		return [
			[ 'Wikipedia ist so super, meine Eltern sagen es ist eine toll Seite. Berlin ist auch Super.' ],
			[ 'Ich heisse Deppendorf ihr und das ist auch gut so!' ],
			[ 'Bei Wikipedia gibts echt tolle Dinge!' ],
			[ 'Ick spend richtig Kohle, denn ick hab ne GmbH & Co.KG' ],
		];
	}

	/**
	 * @dataProvider insultingTestProviderWithRegexChars
	 */
	public function testGivenBadWordMatchContainingRegexChars_validatorReturnsFalse( $commentToTest ): void {
		$textPolicyValidator = $this->getPreFilledTextPolicyValidator();

		$this->assertFalse(
			$textPolicyValidator->hasHarmlessContent(
				$commentToTest,
				TextPolicyValidator::CHECK_URLS
				| TextPolicyValidator::CHECK_URLS_DNS
				| TextPolicyValidator::CHECK_BADWORDS
				| TextPolicyValidator::IGNORE_WHITEWORDS
			)
		);
	}

	public function insultingTestProviderWithRegexChars() {
		return [
			[ 'Ich heisse Deppendorf (ihr Deppen und das ist auch gut so!' ],
			[ 'Ihr [Arschgeigen], ich wohne in //Marsch// und das ist auch gut so!' ],
			[ 'Bei #Wikipedia gibts echt tolle Arschkrampen!' ],
		];
	}

	private function getPreFilledTextPolicyValidator() {
		$textPolicyValidator = new TextPolicyValidator();
		$textPolicyValidator->addBadWordsFromArray(
			[
				'deppen',
				'hitler',
				'fresse',
				'arsch'
			] );
		$textPolicyValidator->addWhiteWordsFromArray(
			[
				'Deppendorf',
				'Marsch',
				'Co.KG',
			] );
		return $textPolicyValidator;
	}

}

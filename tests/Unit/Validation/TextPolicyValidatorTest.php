<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\Validation\TextPolicyValidator;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\TextPolicyValidator
 *
 * @licence GNU GPL v2+
 * @author Christoph Fischer <christoph.fischer@wikimedia.de
 */
class TextPolicyValidatorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider urlTestProvider
	 */
	public function testWhenGivenCommentHasURL_validatorReturnsFalse( $commentToTest ) {
		$textPolicyValidator = new TextPolicyValidator();

		$this->assertFalse( $textPolicyValidator->hasHarmlessContent(
			$commentToTest,
			TextPolicyValidator::CHECK_URLS | TextPolicyValidator::CHECK_URLS_DNS
		) );
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
	public function testWhenGivenHarmlesComment_validatorReturnsTrue( $commentToTest ) {
		$textPolicyValidator = new TextPolicyValidator();

		$this->assertTrue( $textPolicyValidator->hasHarmlessContent(
			$commentToTest,
			TextPolicyValidator::CHECK_URLS | TextPolicyValidator::CHECK_URLS_DNS | TextPolicyValidator::CHECK_BADWORDS
		) );
	}

	public function harmlessTestProvider() {
		return [
			[ 'Ich mag Wikipedia.Wieso ? Weil ich es so toll finde!' ],
			[ 'Wikipedia ist so super, meine Eltern sagen es ist eine toll Seite. Berlin ist auch Super.' ],
			[ 'Ich mag Wikipedia. Aber meine Seite ist auch toll. Googelt mal nach Bunsenbrenner!!!1' ],
			[ 'Bei Wikipedia kann man eine Menge zum Thema Hamster finden. Hamster fressen voll viel Zeug alter!' ],
		];
	}

	/**
	 * @dataProvider insultingTestProvider
	 */
	public function testWhenGivenInsultingComment_validatorReturnsFalse( $commentToTest ) {
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
	public function testWhenGivenInsultingCommentAndWhiteWords_validatorReturnsFalse( $commentToTest ) {
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
	public function testWhenGivenHarmlessCommentAndWhiteWords_validatorReturnsTrue( $commentToTest ) {
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

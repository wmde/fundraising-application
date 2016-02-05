<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\Validation\TextPolicyValidator;

/**
 * @covers WMDE\Fundraising\TextPolicyValidator
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
		return array(
			array( 'www.example.com' ),
			array( 'http://www.example.com' ),
			array( 'https://www.example.com' ),
			array( 'example.com' ),
			array( 'example.com/test' ),
			array( 'example.com/teKAst/index.php' ),
			array( 'Ich mag Wikipedia. Aber meine Seite ist auch toll:example.com/teKAst/index.php' ),
			array( 'inwx.berlin' ),
			array( 'wwwwwww.website.com' ),
			array( 'TriebWerk-Grün.de' ),
		);
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
		return array(
			array( 'Ich mag Wikipedia.Wieso ? Weil ich es so toll finde!' ),
			array( 'Wikipedia ist so super, meine Eltern sagen es ist eine toll Seite. Berlin ist auch Super.' ),
			array( 'Ich mag Wikipedia. Aber meine Seite ist auch toll. Googelt mal nach Bunsenbrenner!!!1' ),
			array( 'Bei Wikipedia kann man eine Menge zum Thema Hamster finden. Hamster fressen voll viel Zeug alter!' ),
		);
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
		return array(
			array( 'Alles Deppen!' ),
			array( 'Heil Hitler!' ),
			array( 'Duhamsterfresse!!!' ),
			array( 'Alles nur HAMSTERFRESSEN!!!!!!!!1111111111' ),
		);
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
		return array(
			array( 'Ich heisse Deppendorf ihr Deppen und das ist auch gut so!' ),
			array( 'Ihr Arschgeigen, ich wohne in Marsch und das ist auch gut so!' ),
			array( 'Bei Wikipedia gibts echt tolle Arschkrampen!' ),
		);
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
		return array(
			array( 'Wikipedia ist so super, meine Eltern sagen es ist eine toll Seite. Berlin ist auch Super.' ),
			array( 'Ich heisse Deppendorf ihr und das ist auch gut so!' ),
			array( 'Bei Wikipedia gibts echt tolle Dinge!' ),
			array( 'Ick spend richtig Kohle, denn ick hab ne GmbH & Co.KG' ),
		);
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

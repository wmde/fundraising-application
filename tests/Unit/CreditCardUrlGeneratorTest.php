<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Presentation\CreditCardUrlConfig;
use WMDE\Fundraising\Frontend\Presentation\CreditCardUrlGenerator;

/**
 * @covers WMDE\Fundraising\Frontend\Presentation\CreditCardUrlGenerator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CreditCardUrlGeneratorTest extends \PHPUnit_Framework_TestCase {

	/** @dataProvider donationProvider */
	public function testUrlGeneration( string $expected, string $firstName, string $lastName, string $payText,
									   int $donationId, string $accessToken, string $updateToken, Euro $amount ) {
		$urlGenerator = new CreditCardUrlGenerator(
			CreditCardUrlConfig::newFromConfig( [
				'base-url' => 'https://credit-card.micropayment.de/creditcard/event/index.php?',
				'project-id' => 'wikimedia',
				'background-color' => 'CCE7CD',
				'skin' => '10h16',
				'theme' => 'wikimedia',
				'testmode' => false
			] )
		);
		$this->assertSame(
			$expected,
			$urlGenerator->generateUrl( $firstName, $lastName, $payText, $donationId, $accessToken, $updateToken, $amount )
		);
	}

	public function testWhenTestModeIsEnabled_urlPassesProperParameter() {
		$urlGenerator = new CreditCardUrlGenerator(
			CreditCardUrlConfig::newFromConfig( [
				'base-url' => 'https://credit-card.micropayment.de/creditcard/event/index.php?',
				'project-id' => 'wikimedia',
				'background-color' => 'CCE7CD',
				'skin' => '10h16',
				'theme' => 'wikimedia',
				'testmode' => true
			] )
		);
		$this->assertSame(
			'https://credit-card.micropayment.de/creditcard/event/index.php?project=wikimedia&bgcolor=CCE7CD&' .
			'paytext=Ich+spende+einmalig&mp_user_firstname=Kai&mp_user_surname=Nissen&sid=1234567&skin=10h16&' .
			'token=my_access_token&utoken=my_update_token&amount=500&theme=wikimedia&testmode=1',
			$urlGenerator->generateUrl(
				'Kai', 'Nissen', 'Ich spende einmalig', 1234567, 'my_access_token', 'my_update_token', Euro::newFromFloat( 5.00 )
			)
		);
	}

	public function donationProvider() {
		return [
			[
				'https://credit-card.micropayment.de/creditcard/event/index.php?project=wikimedia&bgcolor=CCE7CD&' .
					'paytext=Ich+spende+einmalig&mp_user_firstname=Kai&mp_user_surname=Nissen&sid=1234567&skin=10h16&' .
					'token=my_access_token&utoken=my_update_token&amount=500&theme=wikimedia',
				'Kai',
				'Nissen',
				'Ich spende einmalig',
				1234567,
				'my_access_token',
				'my_update_token',
				Euro::newFromFloat( 5.00 )
			],
			[
				'https://credit-card.micropayment.de/creditcard/event/index.php?project=wikimedia&bgcolor=CCE7CD&' .
					'paytext=Ich+spende+monatlich&mp_user_firstname=Kai&mp_user_surname=Nissen&sid=1234567&skin=10h16&' .
					'token=my_access_token&utoken=my_update_token&amount=123&theme=wikimedia',
				'Kai',
				'Nissen',
				'Ich spende monatlich',
				1234567,
				'my_access_token',
				'my_update_token',
				Euro::newFromFloat( 1.23 )
			],
			[
				'https://credit-card.micropayment.de/creditcard/event/index.php?project=wikimedia&bgcolor=CCE7CD&' .
					'paytext=Ich+spende+halbj%C3%A4hrlich&mp_user_firstname=Kai&mp_user_surname=Nissen&sid=1234567&' .
					'skin=10h16&token=my_access_token&utoken=my_update_token&amount=1250&theme=wikimedia',
				'Kai',
				'Nissen',
				'Ich spende halbjährlich',
				1234567,
				'my_access_token',
				'my_update_token',
				Euro::newFromFloat( 12.5 )
			],
		];
	}

}

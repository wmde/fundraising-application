<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests;

use WMDE\Fundraising\Frontend\Presentation\CreditCardConfig;
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
									   int $donationId, string $updateToken, float $amount ) {
		$urlGenerator = new CreditCardUrlGenerator(
			CreditCardConfig::newFromConfig( [
				'base-url' => 'https://credit-card.micropayment.de/creditcard/event/index.php?',
				'project-id' => 'wikimedia',
				'background-color' => 'CCE7CD',
				'skin' => '10h16',
				'theme' => 'wikimedia'
			] )
		);
		$this->assertSame(
			$expected,
			$urlGenerator->generateUrl( $firstName, $lastName, $payText, $donationId, $updateToken, $amount )
		);
	}

	public function donationProvider() {
		return [
			[
				'https://credit-card.micropayment.de/creditcard/event/index.php?project=wikimedia&bgcolor=CCE7CD&' .
					'paytext=Ich+spende+einmalig&mp_user_firstname=Kai&mp_user_surname=Nissen&sid=1234567&skin=10h16&' .
					'utoken=my_update_token&amount=500&theme=wikimedia',
				'Kai',
				'Nissen',
				'Ich spende einmalig',
				1234567,
				'my_update_token',
				5.00
			],
			[
				'https://credit-card.micropayment.de/creditcard/event/index.php?project=wikimedia&bgcolor=CCE7CD&' .
					'paytext=Ich+spende+monatlich&mp_user_firstname=Kai&mp_user_surname=Nissen&sid=1234567&skin=10h16&' .
					'utoken=my_update_token&amount=123&theme=wikimedia',
				'Kai',
				'Nissen',
				'Ich spende monatlich',
				1234567,
				'my_update_token',
				1.23
			],
			[
				'https://credit-card.micropayment.de/creditcard/event/index.php?project=wikimedia&bgcolor=CCE7CD&' .
					'paytext=Ich+spende+halbj%C3%A4hrlich&mp_user_firstname=Kai&mp_user_surname=Nissen&sid=1234567&' .
					'skin=10h16&utoken=my_update_token&amount=1250&theme=wikimedia',
				'Kai',
				'Nissen',
				'Ich spende halbjährlich',
				1234567,
				'my_update_token',
				12.5
			],
		];
	}

}

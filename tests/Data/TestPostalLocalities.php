<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Data;

class TestPostalLocalities {

	public static function data() {
		return [
			(object)[
				'locality' => 'Takeshi\'s Castle',
				'postcode' => '99999',
			],
			(object)[
				'locality' => 'Mushroom Kingdom City',
				'postcode' => '99999',
			],
			(object)[
				'locality' => 'Alabastia',
				'postcode' => '10000',
			],
			(object)[
				'locality' => 'FÜN-Stadt',
				'postcode' => '12000',
			],
			(object)[
				'locality' => 'Ba Sing Se',
				'postcode' => '12300',
			],
			(object)[
				'locality' => 'Satan City',
				'postcode' => '66666',
			],
			(object)[
				'locality' => 'Satan City',
				'postcode' => '66666',
			],
			(object)[
				'locality' => 'Gotham City',
				'postcode' => '12121',
			],
			(object)[
				'locality' => 'Kleinstes-Kaff-der-Welt',
				'postcode' => '45678',
			],
			(object)[
				'locality' => 'Entenhausen',
				'postcode' => '232388',
			],
		];
	}
}

<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\Clock\Clock;

class UserDataKeyGenerator {

	private const START_TIME = '2020-01-01 0:00:00Z';

	private string $masterKey;
	private Clock $time;

	public function __construct( string $masterKey, Clock $time ) {
		$this->masterKey = $masterKey;
		$this->time = $time;
	}

	public function getDailyKey(): string {
		$now = $this->time->now();
		$daysSince2020 = abs( $now->diff( new \DateTimeImmutable( self::START_TIME ) )->days );
		return sodium_bin2base64(
			sodium_crypto_kdf_derive_from_key(
				64,
				$daysSince2020,
				$now->format( 'Ymd' ),
				sodium_base642bin( $this->masterKey, SODIUM_BASE64_VARIANT_ORIGINAL, '' )
			),
			SODIUM_BASE64_VARIANT_ORIGINAL
		);

	}


}

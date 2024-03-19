<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\Clock\Clock;

class UserDataKeyGenerator {

	private const SECONDS_PER_DAY = 86400;

	public function __construct(
		private readonly string $masterKey,
		private readonly Clock $time
	) {
	}

	public function getDailyKey(): string {
		$now = $this->time->now();
		$numberOfDays = intval( $now->getTimestamp() / self::SECONDS_PER_DAY );
		return sodium_bin2base64(
			sodium_crypto_kdf_derive_from_key(
				32,
				$numberOfDays,
				$now->format( 'Ymd' ),
				sodium_base642bin( $this->masterKey, SODIUM_BASE64_VARIANT_ORIGINAL, '' )
			),
			SODIUM_BASE64_VARIANT_ORIGINAL
		);
	}

}

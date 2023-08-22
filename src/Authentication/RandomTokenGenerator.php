<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication;

class RandomTokenGenerator implements TokenGenerator {
	public const MINIMUM_LENGTH = 16;

	public function __construct(
		private readonly int $length
	) {
		if ( $length < self::MINIMUM_LENGTH ) {
			throw new \InvalidArgumentException( 'Token length must be at least ' . self::MINIMUM_LENGTH );
		}
	}

	public function generateToken(): Token {
		return new Token( random_bytes( $this->length ) );
	}
}

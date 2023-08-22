<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication;

class Token {
	public function __construct(
		private readonly string $tokenBytes,
	) {
	}

	public static function fromHex( string $token ): self {
		$bytes = hex2bin( $token );
		if ( $bytes === false ) {
			throw new \InvalidArgumentException( 'Invalid token, was not a hexadecimal value' );
		}
		return new self( $bytes );
	}

	public function __toString(): string {
		return bin2hex( $this->tokenBytes );
	}

	public function getRawBytes(): string {
		return $this->tokenBytes;
	}
}

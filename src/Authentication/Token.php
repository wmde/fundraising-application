<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication;

class Token {
	public function __construct( private readonly string $tokenBytes ) {
	}

	public static function fromHex( string $token ): self {
		// We need to suppress the PHPCS warning for ignoring PHP warnings,
		// because we are using a PHP internal function that may emit a warning
		// We are checking the return type, so we can safely ignore the warning
		$bytes = @hex2bin( $token ); // @codingStandardsIgnoreLine
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

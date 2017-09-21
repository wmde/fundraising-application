<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Domain;

/**
 * @licence GNU GPL v2+
 */
class LessSimpleTransferCodeGenerator implements TransferCodeGenerator {

	public const ALLOWED_CHARACTERS = 'ACDEFKLMNPRTWXYZ349';

	private $characterSource;
	private $checksumGenerator;

	private function __construct( \Iterator $characterSource ) {
		$this->characterSource = $characterSource;

		$this->checksumGenerator = new ChecksumGenerator( str_split( self::ALLOWED_CHARACTERS ) );
	}

	public static function newRandomGenerator(): self {
		return new self(
			( function() {
				$characterCount = strlen( self::ALLOWED_CHARACTERS );
				$characters = str_split( self::ALLOWED_CHARACTERS );
				while ( true ) {
					yield $characters[mt_rand( 0, $characterCount - 1 )];
				}
			} )()
		);
	}

	public static function newDeterministicGenerator( \Iterator $characterSource ): self {
		return new self( $characterSource );
	}

	public function generateTransferCode(): string {
		$code = $this->generateCode() . '-';
		return $code . $this->checksumGenerator->createChecksum( $code );
	}

	private function generateCode(): string {
		return $this->getCharacter()
			. $this->getCharacter()
			. $this->getCharacter()
			. '-'
			. $this->getCharacter()
			. $this->getCharacter()
			. $this->getCharacter();
	}

	private function getCharacter(): string {
		$character = $this->characterSource->current();
		$this->characterSource->next();
		return $character;
	}

	public function transferCodeIsValid( string $code ): bool {
		return $this->formatIsValid( $code )
			&& $this->checksumIsCorrect( $code );
	}

	private function formatIsValid( string $code ): bool {
		$allowedChars = '[' . self::ALLOWED_CHARACTERS . ']';
		$pattern = '/^' . $allowedChars . '{3}-' . $allowedChars . '{3}-' . $allowedChars . '$/';
		return preg_match( $pattern, $code ) === 1;
	}

	private function checksumIsCorrect( string $code ): bool {
		return $this->checksumGenerator->createChecksum( substr( $code, 0, -1 ) )
			=== substr( $code, -1 );
	}

}
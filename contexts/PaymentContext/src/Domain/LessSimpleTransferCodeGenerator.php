<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Domain;

/**
 * @licence GNU GPL v2+
 */
class LessSimpleTransferCodeGenerator implements TransferCodeGenerator {

	public const ALLOWED_CHARACTERS = 'ACDEFKLMNPRSTWXYZ349';

	private $characterSource;
	private $checksumGenerator;

	public function __construct( \Iterator $characterSource ) {
		$this->characterSource = $characterSource;
		$this->checksumGenerator = new ChecksumGenerator( str_split( self::ALLOWED_CHARACTERS ) );
	}

	public function generateTransferCode(): string {
		$code = $this->generateCode() . '-';
		return $code . $this->checksumGenerator->createChecksum( $code );
	}

	public function generateCode(): string {
		return $this->getCharacter()
			. $this->getCharacter()
			. $this->getCharacter()
			. $this->getCharacter()
			. '-'
			. $this->getCharacter()
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
		$pattern = '/^' . $allowedChars . '{4}-' . $allowedChars . '{4}-' . $allowedChars . '$/';
		return preg_match( $pattern, $code ) === 1;
	}

	private function checksumIsCorrect( string $code ): bool {
		return $this->checksumGenerator->createChecksum( substr( $code, 0, -1 ) )
			=== substr( $code, -1 );
	}

}
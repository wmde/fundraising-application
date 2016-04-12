<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

use InvalidArgumentException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
final class Euro {

	private static $DECIMAL_COUNT = 2;
	private static $CENTS_PER_EURO = 100;

	private $cents;

	/**
	 * @param int $cents
	 * @throws InvalidArgumentException
	 */
	private function __construct( int $cents ) {
		if ( $cents < 0 ) {
			throw new InvalidArgumentException( 'Amount needs to be positive' );
		}

		$this->cents = $cents;
	}

	/**
	 * @param int $cents
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public static function newFromCents( int $cents ): self {
		return new self( $cents );
	}

	/**
	 * Constructs a Euro object from a string representation such as "13.37".
	 *
	 * This method takes into account the errors that can arise from floating
	 * point number usage. Amounts with too many decimals are rounded to the
	 * nearest whole euro cent amount.
	 *
	 * @param string $euroAmount
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public static function newFromString( string $euroAmount ): self {
		if ( !is_numeric( $euroAmount ) ) {
			throw new InvalidArgumentException( 'Not a number' );
		}

		return new self( intval(
			round( $euroAmount, self::$DECIMAL_COUNT ) * self::$CENTS_PER_EURO
		) );
	}

	/**
	 * This method takes into account the errors that can arise from floating
	 * point number usage. Amounts with too many decimals are rounded to the
	 * nearest whole euro cent amount.
	 *
	 * @param string $euroAmount
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public static function newFromFloat( float $euroAmount ) {
		return new self( intval(
			round( $euroAmount, self::$DECIMAL_COUNT ) * self::$CENTS_PER_EURO
		) );
	}

	/**
	 * @param int $euroAmount
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public static function newFromInt( int $euroAmount ) {
		return new self( $euroAmount * self::$CENTS_PER_EURO );
	}

	public function getEuroCents(): int {
		return $this->cents;
	}

	public function getEuroFloat(): float {
		return $this->cents / self::$CENTS_PER_EURO;
	}

	/**
	 * Returns the euro amount as string with two decimals always present in format "42.00".
	 */
	public function getEuroString(): string {
		return number_format( $this->getEuroFloat(), self::$DECIMAL_COUNT, '.', '' );
	}

}

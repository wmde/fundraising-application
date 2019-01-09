<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use DateTimeZone;

/**
 * @license GNU GPL v2+
 */
class CampaignDate extends \DateTimeImmutable {

	private const TIMEZONE = 'UTC';

	public function __construct( string $time = 'now', ?DateTimeZone $timezone = null ) {
		if ( $timezone && $timezone->getName() !== self::TIMEZONE ) {
			throw new \InvalidArgumentException( sprintf( 'CampaignDates must have time zone "%s".', self::TIMEZONE ) );
		}
		parent::__construct( $time, new DateTimeZone( self::TIMEZONE ) );
	}

	public static function createFromString( string $time, ?DateTimeZone $timezone = null ): self {
		if ( $timezone === null ) {
			return new self( $time );
		}
		$instance = new \DateTime( $time, $timezone );
		$instance->setTimezone( new DateTimeZone( self::TIMEZONE ) );
		return new self( $instance->format( \DateTime::ISO8601 ) );
	}
}
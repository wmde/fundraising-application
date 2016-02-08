<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Presentation;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 *
 * @todo Make this translatable
 */
class GreetingGenerator {

	public function createGreeting( string $lastName, string $salutation, string $title ): string {
		if ( $lastName === '' ) {
			return 'Sehr geehrte Damen und Herren,';
		}

		$spacedTitle = $title === '' ? '' : $title . ' ';

		switch ( $salutation ) {
			case 'Herr':
				return sprintf( 'Sehr geehrter Herr %s%s,', $spacedTitle, $lastName );
			case 'Frau':
				return sprintf( 'Sehr geehrte Frau %s%s,', $spacedTitle, $lastName );
			case 'Familie':
				return sprintf( 'Sehr geehrte Familie %s%s,', $spacedTitle, $lastName );
			default:
				return 'Sehr geehrte Damen und Herren,';
		}
	}

}
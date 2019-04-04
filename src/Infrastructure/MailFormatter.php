<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * Trim whitespace for each line and multiple blank lines.
 *
 * The text can force explicit line breaks with a literal `\n` char sequence.
 *
 * This is for cleaning up the output of complicated mail templates that have
 * structural indentations that are irrelevant for the final output.
 *
 * @package WMDE\Fundraising\Frontend\Infrastructure
 */
class MailFormatter {

	public static function format( string $message ): string {
		$formattedMessage = '';
		$previousLine = '';
		foreach ( explode( "\n", $message ) as $line ) {
			if ( trim( $line ) === '' && $previousLine === '' ) {
				continue;
			}
			$line = trim( $line );
			$line = str_replace( '\\n', "\n", $line );
			$formattedMessage .= $line . "\n";
			$previousLine = $line;
		}
		return $formattedMessage;
	}
}
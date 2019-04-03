<?php

namespace WMDE\Fundraising\Frontend\Infrastructure;

class MailFormatter {

	public static function format( string $message ): string {
		$formattedMessage = '';
		$previousLine = '';
		foreach ( explode( "\n", $message) as $line ) {
			if ( trim( $line ) === '' && $previousLine === '') {
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
<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

/**
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MailTemplateFilenameTraversable implements \IteratorAggregate {

	public function __construct( private readonly string $mailTemplatePath ) {
	}

	public function getIterator(): \Iterator {
		$glob = glob( $this->mailTemplatePath . '/*\.twig' );
		if ( $glob === false ) {
			throw new \RuntimeException( sprintf( "Failed to find path name: %s",
				var_export( $this->mailTemplatePath . '/*\.twig', true ) ) );
		}
		foreach ( $glob as $fileName ) {
			yield basename( $fileName );
		}
	}

}

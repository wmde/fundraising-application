<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

class MailTemplateFilenameTraversable implements \IteratorAggregate {

	public function __construct( private readonly string $mailTemplatePath ) {
	}

	/**
	 * @return \Iterator<string>
	 */
	public function getIterator(): \Iterator {
		$glob = glob( $this->mailTemplatePath . '/*\.twig' );

		// we can't reliably trigger glob() returning `false` on Linux systems
		// @codeCoverageIgnoreStart
		if ( $glob === false ) {
			throw new \RuntimeException( sprintf( "Failed to find path name: %s",
				var_export( $this->mailTemplatePath . '/*\.twig', true ) ) );
		}

		// @codeCoverageIgnoreEnd

		foreach ( $glob as $fileName ) {
			yield basename( $fileName );
		}
	}

}

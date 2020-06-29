<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Mail;

/**
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MailTemplateFilenameTraversable implements \IteratorAggregate {

	private $mailTemplatePath;

	public function __construct( string $mailTemplatePath ) {
		$this->mailTemplatePath = $mailTemplatePath;
	}

	public function getIterator(): \Iterator {
		foreach ( glob( $this->mailTemplatePath . '/*\.twig' ) as $fileName ) {
			yield basename( $fileName );
		}
	}

}

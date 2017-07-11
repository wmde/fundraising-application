<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MailTemplateFilenameTraversable implements \IteratorAggregate {

	private $mailTemplatePaths;

	public function __construct( array $mailTemplatePaths ) {
		$this->mailTemplatePaths = $mailTemplatePaths;
	}

	public function getIterator(): \Iterator {
		foreach ( $this->mailTemplatePaths as $path ) {
			foreach ( glob( $path . '/Mail_*' ) as $fileName ) {
				yield basename( $fileName );
			}
		}
	}

}
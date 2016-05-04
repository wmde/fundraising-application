<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Content;

use Psr\Log\LoggerInterface;

/**
 * @licence GNU GPL v2+
 */
class PageContentModifier {

	private $logger;
	private $substitutions;

	public function __construct( LoggerInterface $logger, array $substitutions = [] ) {
		$this->logger = $logger;
		$this->substitutions = $substitutions;
	}

	public function getProcessedContent( string $content, string $pageName ): string {
		# full HTML document usually indicates an error (e.g. access denied)
		if ( preg_match( '/^<!DOCTYPE html/', $content ) ) {
			$this->logger->debug( __METHOD__ . ': fail, got error page', [ $content, $pageName ] );
			return '';
		}

		# NOTE: don't strip comments, they may contain javascript, etc!
		# $content = preg_replace('/<!--.*?-->/s', '', $content);
		$content = trim( $content );

		if ( $this->substitutions !== [] ) {
			foreach ( $this->substitutions as $pattern => $value ) {
				$content = preg_replace( $pattern, $value, $content );
			}
		}

		return $content;
	}

}
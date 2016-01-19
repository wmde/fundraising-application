<?php

namespace WMDE\Fundraising\Frontend\Presenters;

use WMDE\Fundraising\Frontend\TwigTemplate;
use WMDE\Fundraising\Frontend\UseCases\DisplayPage\PageDisplayResponse;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DisplayPagePresenter {

	private $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function present( PageDisplayResponse $displayResponse ): string {
		return $this->template->render( [
			'header' => $this->getContentOrMissingMessage( $displayResponse->getHeaderContent(), 'header' ),
			'main' => $this->getContentOrMissingMessage( $displayResponse->getMainContent(), 'main content' ),
			'footer' => $this->getContentOrMissingMessage( $displayResponse->getFooterContent(), 'footer' ),
			'noJsNotice' => $this->getContentOrMissingMessage( $displayResponse->getNoJsNoticedContent(), 'no JS notice' ),
		] );
	}

	private function getContentOrMissingMessage( string $content, string $contentName ): string {
		if ( $content === '' ) {
			return htmlspecialchars( "Could not load $contentName!" );
		}

		return $content;
	}

}
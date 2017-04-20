<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Presentation\ContentPage;

use Twig_Environment;
use Twig_Error_Loader;
use WMDE\Fundraising\HtmlFilter\PurifierInterface;

class ContentProvider {
	private $environment;
	private $htmlPurifier;

	public function __construct( Twig_Environment $environment, PurifierInterface $purifier ) {
		$this->environment = $environment;
		$this->htmlPurifier = $purifier;
	}

	public function render( string $pageId ): string {
		try {
			$html = $this->environment->render( $pageId . '.twig' );
		} catch ( Twig_Error_Loader $exception ) {
			throw new ContentNotFoundException( "Template for page '$pageId' not found'", 0, $exception );
		}

		return $this->htmlPurifier->purify( $html );
	}
}

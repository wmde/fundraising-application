<?php

namespace WMDE\Fundraising\Frontend\Presentation\ContentPage;

use Twig_Environment;
use Twig_Error_Loader;
use WMDE\Fundraising\HtmlFilter\PurifierInterface;

class ContentProvider {

	/**
	 * @var Twig_Environment
	 */
	private $environment;
	/**
	 * @var PurifierInterface
	 */
	private $purifier;

	public function __construct( Twig_Environment $environment, PurifierInterface $purifier = null ) {
		$this->environment = $environment;
		$this->purifier = $purifier;
	}

	public function render( string $contentId, array $context = [] ): string {
		try {
			$content = $this->environment->render( $contentId . '.twig', $context );
		} catch ( Twig_Error_Loader $exception ) {
			throw new ContentNotFoundException( "Template '$contentId' not found'", 0, $exception );
		}

		if (!is_null($this->purifier)) {
			$content = $this->purifier->purify($content);
		}

		return $content;
	}

}
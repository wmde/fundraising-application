<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Silex\Application;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\ContentPage\ContentNotFoundException;
use WMDE\Fundraising\Frontend\Presentation\ContentPage\PageNotFoundException;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * @license GNU GPL v2+
 * @author Tim Eulitz < tim.eulitz@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class PageDisplayHandler {

	private $ffFactory;
	private $app;

	public function __construct( FunFunFactory $ffFactory, Application $app ) {
		$this->ffFactory = $ffFactory;
		$this->app = $app;
	}

	public function handle( $pageName ) {
		$pageSelector = $this->ffFactory->getContentPagePageSelector();

		try {
			$pageId = $pageSelector->getPageId( $pageName );
		}
		catch ( PageNotFoundException $exception ) {
			throw new NotFoundHttpException( "Page page name '$pageName' not found." );
		}

		try {
			return $this->getPageTemplate( $pageId )->render( [ 'page_id' => $pageId ] );
		}
		catch ( \Twig_Error_Runtime $exception ) {
			if ( $exception->getPrevious() instanceof ContentNotFoundException ) {
				throw new NotFoundHttpException( "Content for page id '$pageId' not found." );
			}

			throw $exception;
		}
	}

	/**
	 * Checks if file matching page ID exists in page_layouts directory and loads template if it exists
	 * Otherwise, falls back to base.twig template
	 *
	 * @param string $pageId
	 * @param array $context Additional variables for template
	 *
	 * @return TwigTemplate
	 */
	public function getPageTemplate( string $pageId, array $context = [] ): TwigTemplate {
		$template = 'page_layouts' . DIRECTORY_SEPARATOR . 'base.html.twig';
		$pageTemplate = 'page_layouts' . DIRECTORY_SEPARATOR . $pageId . '.html.twig';

		if ( file_exists( $this->ffFactory->getAbsoluteSkinDirectory() . DIRECTORY_SEPARATOR . $pageTemplate ) ) {
			$template = $pageTemplate;
		}

		return $this->ffFactory->getLayoutTemplate($template, $context);
	}
}
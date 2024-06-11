<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\StaticContent;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Error\RuntimeError;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\ContentPage\ContentNotFoundException;
use WMDE\Fundraising\Frontend\Presentation\ContentPage\PageNotFoundException;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

class PageDisplayController {

	private FunFunFactory $ffFactory;

	public function index( FunFunFactory $ffFactory, string $pageName ): Response {
		$this->ffFactory = $ffFactory;

		$pageSelector = $this->ffFactory->getContentPagePageSelector();

		try {
			$pageId = $pageSelector->getPageId( $pageName );
		} catch ( PageNotFoundException $exception ) {
			throw new NotFoundHttpException( "Page page name '$pageName' not found." );
		}

		try {
			return new Response( $this->getPageTemplate( $pageId )->render( [ 'page_id' => $pageId ] ) );
		} catch ( RuntimeError $exception ) {
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

		if ( file_exists( $this->ffFactory->getSkinDirectory() . DIRECTORY_SEPARATOR . $pageTemplate ) ) {
			$template = $pageTemplate;
			$context = $this->getAdditionalContextForPageId( $pageId, $context );
		}

		return $this->ffFactory->getLayoutTemplate( $template, $context );
	}

	/**
	 * @param string $pageId
	 * @param array<string, mixed> $context
	 */
	private function getAdditionalContextForPageId( string $pageId, array $context ): array {
		if ( $pageId === 'supporters' ) {
			$context['supporters'] = $this->ffFactory->getSupportersList();
		}
		return $context;
	}
}

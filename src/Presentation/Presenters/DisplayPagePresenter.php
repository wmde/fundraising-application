<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

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
		$mainTemplate = '404message.html.twig';
		if ( $displayResponse->getTemplateExists() ) {
			$mainTemplate = $displayResponse->getMainContentTemplate();
		}
		return $this->template->render( [
			'main_template' => $mainTemplate,
		] );
	}
}
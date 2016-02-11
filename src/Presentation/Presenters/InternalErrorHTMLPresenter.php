<?php

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\TwigTemplate;

/**
 * Render an error page
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class InternalErrorHTMLPresenter {

	private $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function present( \Exception $exception ): string {
		return $this->template->render( [ 'message' => $exception->getMessage() ] );
	}

}
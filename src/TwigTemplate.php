<?php

namespace WMDE\Fundraising\Frontend;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TwigTemplate {

	private $twig;
	private $templatePath;

	public function __construct( \Twig_Environment $twig, string $templatePath ) {
		$this->twig = $twig;
		$this->templatePath = $templatePath;
	}

	public function render( array $arguments ): string {
		return $this->twig->render( $this->templatePath, $arguments );
	}

}
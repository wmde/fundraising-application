<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

/**
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TwigTemplate {

	private $twig;
	private $templatePath;
	private $context;

	public function __construct( \Twig_Environment $twig, string $templatePath, array $context = [] ) {
		$this->twig = $twig;
		$this->templatePath = $templatePath;
		$this->context = $context;
	}

	public function render( array $arguments ): string {
		return $this->twig->render( $this->templatePath, $arguments + $this->context );
	}

}

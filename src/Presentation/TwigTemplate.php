<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use Twig\Environment;

/**
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TwigTemplate {

	public function __construct(
		private readonly Environment $twig,
		private readonly string $templatePath,
		private readonly array $context = []
	) {
	}

	public function render( array $arguments ): string {
		return $this->twig->render( $this->templatePath, $arguments + $this->context );
	}

}

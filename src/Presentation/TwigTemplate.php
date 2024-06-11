<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use Twig\Environment;

class TwigTemplate {

	/**
	 * @param Environment $twig
	 * @param string $templatePath
	 * @param array<string, mixed> $context
	 */
	public function __construct(
		private readonly Environment $twig,
		private readonly string $templatePath,
		private readonly array $context = []
	) {
	}

	/**
	 * @param array<string, mixed> $arguments
	 */
	public function render( array $arguments ): string {
		return $this->twig->render( $this->templatePath, $arguments + $this->context );
	}

}

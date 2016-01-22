<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend;

use Twig_Loader_Chain;
use Twig_Loader_Array;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TwigTemplate {

	private $twig;
	private $templatePath;

	public function __construct( \Twig_Environment $twig, string $templatePath, array $context = [] ) {
		$this->twig = $twig;
		$this->templatePath = $templatePath;
		$this->context = $context;
	}

	public function render( array $arguments ): string {
		return $this->twig->render( $this->templatePath, $arguments + $this->context );
	}

	/**
	 * When using this method, arguments can be rendered as templates by
	 * using {% include 'argumentName' %} instead of {$ argumentName $}.
	 *
	 * @param array $arguments
	 * @return string
	 */
	public function renderArgumentsAsTemplates( array $arguments ): string {
		$loader = new Twig_Loader_Chain( [
			new Twig_Loader_Array( $arguments ),
			$this->twig->getLoader()
		] );
		$this->twig->setLoader( $loader );
		return $this->twig->render( $this->templatePath, $this->context );
	}

}
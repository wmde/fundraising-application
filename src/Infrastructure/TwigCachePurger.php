<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TwigCachePurger implements CachePurger {

	private $twigEnvironment;

	public function __construct( \Twig_Environment $twigEnvironment ) {
		$this->twigEnvironment = $twigEnvironment;
	}

	/**
	 * @throws CachePurgingException
	 */
	public function purgeCache() {
		$this->twigEnvironment->clearCacheFiles();
		$this->twigEnvironment->clearTemplateCache();
	}

}

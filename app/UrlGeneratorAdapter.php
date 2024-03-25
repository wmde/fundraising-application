<?php
declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;

class UrlGeneratorAdapter implements UrlGenerator {

	public function __construct( private readonly UrlGeneratorInterface $urlGenerator ) {
	}

	/**
	 * @param string $routeName
	 * @param array<string, string> $parameters
	 */
	public function generateAbsoluteUrl( string $routeName, array $parameters = [] ): string {
		return $this->urlGenerator->generate(
			$routeName,
			$parameters,
			UrlGeneratorInterface::ABSOLUTE_URL
		);
	}

	public function generateRelativeUrl( string $routeName, array $parameters = [] ): string {
		return $this->urlGenerator->generate(
			$routeName,
			$parameters,
			UrlGeneratorInterface::ABSOLUTE_PATH
		);
	}

}

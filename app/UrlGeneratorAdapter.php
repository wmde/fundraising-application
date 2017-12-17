<?php
declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;

class UrlGeneratorAdapter implements UrlGenerator {

	private $urlGenerator;

	public function __construct( UrlGeneratorInterface $urlGenerator ) {
		$this->urlGenerator = $urlGenerator;
	}

	public function generateUrl( string $name, array $parameters = [] ): string {
		return $this->urlGenerator->generate( $name, $parameters );
	}

}
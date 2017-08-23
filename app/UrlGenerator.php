<?php
declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App;

use Symfony\Bridge\Twig\Extension\RoutingExtension;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator as UrlGeneratorInterface;

class UrlGenerator implements UrlGeneratorInterface {
	private $routingExtension;

	public function __construct( RoutingExtension $routingExtension ) {
		$this->routingExtension = $routingExtension;
	}

	public function generateUrl( string $name, array $parameters = [] ): string {
		return $this->routingExtension->getUrl( $name, $parameters );
	}
}
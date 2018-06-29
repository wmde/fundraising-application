<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App;

use Generator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * Make FunFunFactory an injectable argument for Silex routes and controllers
 *
 * Inspired by https://github.com/derrabus/silex-psr11-provider
 *
 * @license GNU GPL v2+
 */
class FundraisingFactoryServiceProvider implements ServiceProviderInterface {

	private $fffactory;

	public function __construct( FunFunFactory $fffactory ) {
		$this->fffactory = $fffactory;
	}

	public function register( Container $pimple ) {
		$pimple['fundraising_factory'] = function ( Container $c ) {
			return $this->fffactory;
		};
		$pimple->extend( 'argument_value_resolvers', function ( array $resolvers, Container $c ) {
			$resolvers[] = new class( $c['fundraising_factory'] ) implements ArgumentValueResolverInterface {

				private $ffFactory;

				public function __construct( FunFunFactory $ffFactory ) {
					$this->ffFactory = $ffFactory;
				}

				public function supports( Request $request, ArgumentMetadata $argument ): bool {
					return $argument->getType() === FunFunFactory::class;
				}

				public function resolve( Request $request, ArgumentMetadata $argument ): Generator {
					yield $this->ffFactory;
				}
			};

			return $resolvers;
		} );
	}
}
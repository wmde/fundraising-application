<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Validation;

use FileFetcher\SimpleFileFetcher;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;

class FeatureToggleParser {

	/** @see \WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle::featureIsActive */
	private const FEATURE_TOGGLE_METHOD_NAME = 'featureIsActive';

	/**
	 * @param string $choiceFactoryLocation
	 * @return string[]
	 */
	public static function getFeatureToggleChecks( string $choiceFactoryLocation ): array {
		$featureToggleChecks = [];
		foreach ( self::parseMethodCalls( $choiceFactoryLocation ) as $featureToggleCheck ) {
			$args = $featureToggleCheck->getArgs();
			if ( count( $args ) !== 1 ) {
				throw new \LogicException( self::FEATURE_TOGGLE_METHOD_NAME . ' should have exactly one argument.' );
			}

			$argument = $args[0];
			$value = $argument->value;

			if ( !( $value instanceof Node\Scalar\String_ ) ) {
				throw new \LogicException( self::FEATURE_TOGGLE_METHOD_NAME . ' argument should be a string.' );
			}

			$featureToggleChecks[] = $value->value;
		}

		return $featureToggleChecks;
	}

	/**
	 * @param string $choiceFactoryLocation
	 * @return MethodCall[]
	 */
	private static function parseMethodCalls( string $choiceFactoryLocation ): array {
		$parser = ( new ParserFactory() )->createForVersion( PhpVersion::fromComponents( 8, 3 ) );
		$nodeFinder = new NodeFinder();
		$choiceFactoryCode = ( new SimpleFileFetcher() )->fetchFile( $choiceFactoryLocation );
		$syntaxTree = $parser->parse( $choiceFactoryCode );
		if ( $syntaxTree === null ) {
			throw new \RuntimeException( 'Parser returned null' );
		}
		return $nodeFinder->find(
			$syntaxTree,
			static function ( Node $node ) {
				return $node instanceof MethodCall
					&& $node->name instanceof Identifier
					&& $node->name->toString() === self::FEATURE_TOGGLE_METHOD_NAME;
			}
		);
	}
}

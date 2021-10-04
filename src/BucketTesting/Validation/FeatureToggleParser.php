<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Validation;

use FileFetcher\SimpleFileFetcher;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\VariadicPlaceholder;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;

/**
 * @license GPL-2.0-or-later
 */
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
			if ( count( $featureToggleCheck->args ) !== 1 ) {
				throw new \LogicException( self::FEATURE_TOGGLE_METHOD_NAME . ' should have exactly one argument.' );
			}
			/** @var Arg|VariadicPlaceholder $argument */
			$argument = $featureToggleCheck->args[0]->value;
			if ( ( $argument instanceof VariadicPlaceholder ) ) {
				throw new \LogicException( self::FEATURE_TOGGLE_METHOD_NAME . ' should have exactly one argument, not a variadic argument.' );
			}
			$featureToggleChecks[] = $argument->value;
		}
		return $featureToggleChecks;
	}

	/**
	 * @param string $choiceFactoryLocation
	 * @return MethodCall[]
	 */
	private static function parseMethodCalls( string $choiceFactoryLocation ): array {
		$parser = ( new ParserFactory() )->create( ParserFactory::PREFER_PHP7 );
		$nodeFinder = new NodeFinder();
		$choiceFactoryCode = ( new SimpleFileFetcher() )->fetchFile( $choiceFactoryLocation );
		$syntaxTree = $parser->parse( $choiceFactoryCode );
		return $nodeFinder->find(
			$syntaxTree,
			static function ( Node $node ) {
				return $node instanceof MethodCall && $node->name->toString() === self::FEATURE_TOGGLE_METHOD_NAME;
			}
		);
	}
}

<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Validation;

use FileFetcher\SimpleFileFetcher;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;

/**
 * @license GNU GPL v2+
 */
class FeatureToggleParser {

	/** @see \WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle::featureIsActive */
	const FEATURE_TOGGLE_METHOD_NAME = 'featureIsActive';

	/**
	 * @return string[]
	 */
	public static function getFeatureToggleChecks( string $choiceFactoryLocation ): array {
		$featureToggleChecks = [];
		foreach ( self::parseMethodCalls( $choiceFactoryLocation ) as $featureToggleCheck ) {
			if ( count( $featureToggleCheck->args ) !== 1 ) {
				throw new \LogicException( self::FEATURE_TOGGLE_METHOD_NAME . ' should have exactly one argument.' );
			}
			$featureToggleChecks[] = $featureToggleCheck->args[0]->value->value;
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
			function ( Node $node ) {
				return $node instanceof MethodCall && $node->name->toString() === self::FEATURE_TOGGLE_METHOD_NAME;
			}
		);
	}
}
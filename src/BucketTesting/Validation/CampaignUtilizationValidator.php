<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Validation;

use FileFetcher\SimpleFileFetcher;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;

/**
 * @license GNU GPL v2+
 */
class CampaignUtilizationValidator {

	/** @see \WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle::featureIsActive */
	const FEATURE_TOGGLE_METHOD_NAME = 'featureIsActive';

	private $hasValidated = false;
	private $campaignCollection;
	private $ignoredBuckets;
	private $choiceFactoryLocation;
	private $errorLogger;

	public function __construct(
		CampaignCollection $campaignCollection,
		array $ignoredBuckets,
		string $choiceFactoryLocation,
		ValidationErrorLogger $errorLogger
	) {
		$this->campaignCollection = $campaignCollection;
		$this->errorLogger = $errorLogger;
		$this->ignoredBuckets = $ignoredBuckets;
		$this->choiceFactoryLocation = $choiceFactoryLocation;
	}

	public function isPassing(): bool {
		return empty( $this->getErrors() );
	}

	public function getErrors(): array {
		$this->validate();

		return $this->errorLogger->getErrors();
	}

	private function validate(): void {
		if ( $this->hasValidated ) {
			return;
		}

		$featureToggleChecks = $this->getFeatureToggleChecks();

		$this->checkForDuplicateFeatureToggles( $featureToggleChecks );
		$featureToggleChecks = array_unique( $featureToggleChecks );

		$configBucketIdList = $this->buildBucketIdsFromCampaignCollection(
			$this->campaignCollection
		);

		/** Remove all buckets which were marked as test buckets in TEST_BUCKET_IGNORE_LIST */
		$featureToggleChecks = array_filter( $featureToggleChecks, [ __CLASS__, 'filterBucketIds' ] );
		$configBucketIdList = array_filter( $configBucketIdList, [ __CLASS__, 'filterBucketIds' ] );

		/** Check if either the configuration files or the choice factory has rules not found in the other */
		$codeConfigDiff = array_diff( $featureToggleChecks, $configBucketIdList );
		$configCodeDiff = array_diff( $configBucketIdList, $featureToggleChecks );

		$this->hasValidated = true;

		if ( empty( $codeConfigDiff ) === true && empty( $configCodeDiff ) === true ) {
			return;
		}

		foreach ( $codeConfigDiff as $missingBucket ) {
			$this->errorLogger->addError(
				'Feature toggle check for ' . $missingBucket . ' is implemented but no campaign configuration can be found.'
			);
		}
		foreach ( $configCodeDiff as $featureToggleCheck ) {
			$this->errorLogger->addError(
				'Bucket ' . $featureToggleCheck . ' is configured but no implementation can be found in ChoiceFactory.'
			);
		}
	}

	private function getFeatureToggleChecks(): array {
		$featureToggleChecks = [];
		foreach ( $this->parseMethodCalls() as $featureToggleCheck ) {
			if ( count( $featureToggleCheck->args ) !== 1 ) {
				throw new \LogicException( self::FEATURE_TOGGLE_METHOD_NAME . ' should have exactly one argument.' );
			}
			$featureToggleChecks[] = $featureToggleCheck->args[0]->value->value;
		}
		return $featureToggleChecks;
	}

	private function parseMethodCalls(): array {
		$parser = ( new ParserFactory() )->create( ParserFactory::PREFER_PHP7 );
		$nodeFinder = new NodeFinder();
		$choiceFactoryCode = ( new SimpleFileFetcher() )->fetchFile( $this->choiceFactoryLocation );
		$syntaxTree = $parser->parse( $choiceFactoryCode );
		return $nodeFinder->find(
			$syntaxTree,
			function ( Node $node ) {
				return $node instanceof MethodCall && $node->name->toString() === self::FEATURE_TOGGLE_METHOD_NAME;
			}
		);
	}

	private function buildBucketIdsFromCampaignCollection( CampaignCollection $campaignCollection ): array {
		$buckets = [];
		/** @var Campaign $campaign */
		foreach ( $campaignCollection as $campaign ) {
			foreach ( $campaign->getBuckets() as $bucket ) {
				$buckets[] = $bucket->getId();
			}
		}
		return $buckets;
	}

	private function filterBucketIds( string $bucketId ): bool {
		return !(in_array( $bucketId, $this->ignoredBuckets ));
	}

	private function checkForDuplicateFeatureToggles( array $featureToggleChecks ) {
		if ( $duplicates = $this->getDuplicates( $featureToggleChecks ) ) {
			foreach ( $duplicates as $duplicate ) {
				$this->errorLogger->addError(
					'ChoiceFactory contains multiple checks for feature toggle: ' . $duplicate
				);
			}
		}
	}

	private function getDuplicates( array $values ): array {
		return array_unique( array_diff_assoc( $values, array_unique( $values ) ) );
	}
}
<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Validation;

use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;

/**
 * @license GNU GPL v2+
 */
class CampaignUtilizationValidator {

	private $hasValidated = false;
	private $campaignCollection;
	private $ignoredBuckets;
	private $featureToggles;
	private $errorLogger;

	public function __construct(
		CampaignCollection $campaignCollection,
		array $ignoredBuckets,
		array $featureToggles,
		ValidationErrorLogger $errorLogger
	) {
		$this->campaignCollection = $campaignCollection;
		$this->errorLogger = $errorLogger;
		$this->ignoredBuckets = $ignoredBuckets;
		$this->featureToggles = $featureToggles;
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

		$this->findInconsistentFeatureToggleOccurrences();

		$filteredFeatureToggleChecks = array_unique( $this->featureToggles );
		$configBucketIdList = $this->buildBucketIdsFromCampaignCollection(
			$this->campaignCollection
		);

		/** Remove all buckets which were marked as test buckets in TEST_BUCKET_IGNORE_LIST */
		$filteredFeatureToggleChecks = array_filter( $filteredFeatureToggleChecks, [ __CLASS__, 'filterBucketIds' ] );
		$configBucketIdList = array_filter( $configBucketIdList, [ __CLASS__, 'filterBucketIds' ] );

		/** Check if either the configuration files or the choice factory has rules not found in the other */
		$codeConfigDiff = array_diff( $filteredFeatureToggleChecks, $configBucketIdList );
		$configCodeDiff = array_diff( $configBucketIdList, $filteredFeatureToggleChecks );

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
		return !( in_array( $bucketId, $this->ignoredBuckets ) );
	}

	/**
	 * This method finds 'inconsistent' number of implementations for feature toggles checks
	 * If you have implemented a check for a specific campaign bucket twice and only once for
	 * a bucket of the same campaign, the ChoiceFactory is missing a code path for the campaign
	 */
	private function findInconsistentFeatureToggleOccurrences(): void {
		$campaignBuckets = [];
		foreach ( array_count_values( $this->featureToggles ) as $featureToggle => $featureToggleCount ) {
			$campaign = $this->getCampaignNameFromFeatureToggle( $featureToggle );
			if ( isset( $campaignBuckets[$campaign] ) && $campaignBuckets[$campaign] !== $featureToggleCount ) {
				$this->errorLogger->addError(
					'Campaign buckets for "' . $featureToggle . '" have not been implemented consistently.'
				);
				continue;
			}
			$campaignBuckets[$this->getCampaignNameFromFeatureToggle( $featureToggle )] = $featureToggleCount;
		}
	}

	private function getCampaignNameFromFeatureToggle( string $featureToggle ): string {
		return substr( $featureToggle, 0, strrpos( $featureToggle, '.' ) );
	}
}
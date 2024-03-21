<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Validation;

use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule\CampaignValidationRuleInterface;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule\DefaultBucketRule;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule\MinBucketCountRule;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule\StartAndEndTimeRule;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule\UniqueBucketRule;

/**
 * Validates campaign data by looking for inconsistencies and logical errors
 * YAML validity is checked in during loading of campaign configuration
 *
 * @see \WMDE\Fundraising\Frontend\BucketTesting\CampaignConfiguration
 */
class CampaignValidator {

	private bool $hasValidated = false;

	/** @var CampaignValidationRuleInterface[] */
	private array $rules = [];

	public function __construct(
		private readonly CampaignCollection $campaignCollection,
		private readonly CampaignErrorCollection $errorLogger
	) {
		$this->rules = [ new DefaultBucketRule(), new StartAndEndTimeRule(), new UniqueBucketRule(), new MinBucketCountRule() ];
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

		foreach ( $this->campaignCollection as $campaign ) {
			$this->validateCampaign( $campaign );
		}

		$this->hasValidated = true;
	}

	private function validateCampaign( Campaign $campaign ): void {
		foreach ( $this->rules as $rule ) {
			$rule->validate( $campaign, $this->errorLogger );
		}
	}
}

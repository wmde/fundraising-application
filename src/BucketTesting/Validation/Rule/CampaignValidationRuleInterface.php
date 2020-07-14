<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule;

use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignErrorCollection;

/**
 * @license GPL-2.0-or-later
 */
interface CampaignValidationRuleInterface {
	public function validate( Campaign $campaign, CampaignErrorCollection $errorLogger ): bool;
}

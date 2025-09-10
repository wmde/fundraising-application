<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Membership;

/**
 * @codeCoverageIgnore
 */
enum MembershipFeeUpgradeFrontendFlag: string {

	case SHOW_FEE_CHANGE_FORM = 'SHOW_FEE_CHANGE_FORM';
	case SHOW_FEE_ALREADY_CHANGED_PAGE = 'SHOW_FEE_ALREADY_CHANGED_PAGE';
	case SHOW_ERROR_PAGE = 'SHOW_ERROR_PAGE';
}

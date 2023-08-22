<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication;

enum AuthenticationBoundedContext: string
{
	case Donation = 'D';
	case Membership = 'M';
}

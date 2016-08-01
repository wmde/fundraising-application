<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\PaymentContext\Infrastructure\CreditCardExpiry;
use WMDE\Fundraising\Frontend\PaymentContext\Infrastructure\CreditCardService;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class FakeCreditCardService implements CreditCardService {

	public function getExpirationDate( string $customerId ): CreditCardExpiry {
		return new CreditCardExpiry( 9, 2038 );
	}

}

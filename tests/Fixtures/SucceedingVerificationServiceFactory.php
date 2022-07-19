<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\PaymentContext\Domain\Model\Payment;
use WMDE\Fundraising\PaymentContext\Services\ExternalVerificationService\SucceedingVerificationService;
use WMDE\Fundraising\PaymentContext\UseCases\BookPayment\VerificationService;
use WMDE\Fundraising\PaymentContext\UseCases\BookPayment\VerificationServiceFactory;

class SucceedingVerificationServiceFactory implements VerificationServiceFactory {

	public function create( Payment $payment ): VerificationService {
		return new SucceedingVerificationService();
	}
}

<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\PaymentContext\Domain\Model\Payment;
use WMDE\Fundraising\PaymentContext\UseCases\BookPayment\VerificationService;
use WMDE\Fundraising\PaymentContext\UseCases\BookPayment\VerificationServiceFactory;

class FailingVerificationServiceFactory implements VerificationServiceFactory {

	public function __construct( private readonly string $errorMessage ) {
	}

	public function create( Payment $payment ): VerificationService {
		return new FailingPayPalVerificationService( $this->errorMessage );
	}
}

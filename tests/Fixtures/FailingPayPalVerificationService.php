<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\PaymentContext\UseCases\BookPayment\VerificationResponse;
use WMDE\Fundraising\PaymentContext\UseCases\BookPayment\VerificationService;

class FailingPayPalVerificationService implements VerificationService {

	public function __construct( private readonly string $errorMessage ) {
	}

	public function validate( array $transactionData ): VerificationResponse {
		return VerificationResponse::newFailureResponse( $this->errorMessage );
	}
}

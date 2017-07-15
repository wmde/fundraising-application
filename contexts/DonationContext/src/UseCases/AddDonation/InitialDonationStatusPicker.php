<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentType;

class InitialDonationStatusPicker {

	public function __invoke( string $paymentType ): string {
		if ( $paymentType === PaymentType::DIRECT_DEBIT ) {
			return Donation::STATUS_NEW;
		} elseif ( $paymentType === PaymentType::BANK_TRANSFER ) {
			return Donation::STATUS_PROMISE;
		} elseif ( $paymentType === PaymentType::SOFORT ) {
			return Donation::STATUS_PROMISE;
		}

		return Donation::STATUS_EXTERNAL_INCOMPLETE;
	}
}

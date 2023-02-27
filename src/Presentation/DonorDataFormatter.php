<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor\AnonymousDonor;

class DonorDataFormatter {

	public function getAddressArguments( Donation $donation ): array {
		if ( $donation->getDonor() instanceof AnonymousDonor ) {
			return [
				'isAnonymous' => true
			];
		}
		return array_merge(
			$donation->getDonor()->getName()->toArray(),
			[
				'fullName' => $donation->getDonor()->getName()->getFullName(),
				'street' => $donation->getDonor()->getPhysicalAddress()->getStreetAddress(),
				'postcode' => $donation->getDonor()->getPhysicalAddress()->getPostalCode(),
				'city' => $donation->getDonor()->getPhysicalAddress()->getCity(),
				'country' => $donation->getDonor()->getPhysicalAddress()->getCountryCode(),
				'email' => $donation->getDonor()->getEmailAddress()
			] );
	}

	public function getDonationDate(): string {
		return ( new \DateTime() )->format( 'd.m.Y' );
	}

	public function getHideBannerCookieDuration(): string {
		// 180 days
		return '15552000';
	}
}

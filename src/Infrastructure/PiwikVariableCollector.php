<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;

/**
 * Generates `PiwikEvents` objects
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PiwikVariableCollector {

	public static function newForDonation( array $sessionTrackingData, Donation $donation ): PiwikEvents {
		$piwikEvents = new PiwikEvents();
		if ( isset( $sessionTrackingData['paymentType'] ) ) {
			$piwikEvents->triggerSetCustomVariable(
				PiwikEvents::CUSTOM_VARIABLE_PAYMENT_TYPE,
				$sessionTrackingData['paymentType'] . '/' . $donation->getPaymentType(),
				PiwikEvents::SCOPE_VISIT
			);
		}
		if ( isset( $sessionTrackingData['paymentAmount'] ) ) {
			$piwikEvents->triggerSetCustomVariable(
				PiwikEvents::CUSTOM_VARIABLE_AMOUNT,
				$sessionTrackingData['paymentAmount'] . '/' . $donation->getAmount()->getEuroString(),
				PiwikEvents::SCOPE_VISIT
			);
		}
		if ( isset( $sessionTrackingData['paymentInterval'] ) ) {
			$piwikEvents->triggerSetCustomVariable(
				PiwikEvents::CUSTOM_VARIABLE_PAYMENT_INTERVAL,
				$sessionTrackingData['paymentInterval'] . '/' . $donation->getPaymentIntervalInMonths(),
				PiwikEvents::SCOPE_VISIT
			);
		}
		return $piwikEvents;
	}

}
